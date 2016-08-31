<?php

namespace cornernote\workflow\manager\models\form;

use cornernote\workflow\manager\models\Metadata;
use cornernote\workflow\manager\models\Status;
use Yii;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\widgets\ActiveForm;

/**
 * Class StatusForm
 *
 * @property Status $status
 * @property Metadata[] $metadatas
 *
 * @package cornernote\workflow\manager\models\form
 */
class StatusForm extends Model
{
    /**
     * @var Status
     */
    private $_status;
    /**
     * @var Metadata[]
     */
    private $_metadatas;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['Status'], 'required'],
            [['Metadatas'], 'safe'],
        ];
    }

    /**
     *
     */
    public function afterValidate()
    {
        if (!Model::validateMultiple($this->getAllModels())) {
            $this->addError(null); // add an empty error to prevent saving
        }
        parent::afterValidate();
    }

    /**
     * @return bool
     * @throws \yii\db\Exception
     */
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }
        $transaction = Yii::$app->db->beginTransaction();
        if (!$this->status->save()) {
            $transaction->rollBack();
            return false;
        }
        if (!$this->saveMetadatas()) {
            $transaction->rollBack();
            return false;
        }
        $transaction->commit();
        return true;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function saveMetadatas()
    {
        $keep = [];
        foreach ($this->metadatas as $metadata) {
            $metadata->status_id = $this->status->id;
            $metadata->workflow_id = $this->status->workflow_id;
            if (!$metadata->save(false)) {
                return false;
            }
            $keep[] = $metadata->key;
        }
        $query = Metadata::find()->andWhere(['status_id' => $this->status->id, 'workflow_id' => $this->status->workflow_id]);
        if ($keep) {
            $query->andWhere(['not in', 'key', $keep]);
        }
        foreach ($query->all() as $metadata) {
            $metadata->delete();
        }
        return true;
    }

    /**
     * @return Status
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * @param Status|array $status
     */
    public function setStatus($status)
    {
        if ($status instanceof Status) {
            $this->_status = $status;
        } else if (is_array($status)) {
            $this->_status->setAttributes($status);
        }
    }

    /**
     * @return Metadata[]
     */
    public function getMetadatas()
    {
        if ($this->_metadatas === null) {
            $this->_metadatas = $this->status->isNewRecord ? [] : $this->status->metadatas;
        }
        return $this->_metadatas;
    }

    /**
     * @param $key
     * @return Metadata
     */
    private function getMetadata($key)
    {
        $metadata = $key && strpos($key, 'new') === false ? Metadata::findOne(['key' => $key, 'status_id' => $this->status->id, 'workflow_id' => $this->status->workflow_id]) : false;
        if (!$metadata) {
            $metadata = new Metadata();
            $metadata->loadDefaultValues();
        }
        return $metadata;
    }

    /**
     * @param Metadata[]|array $metadatas
     */
    public function setMetadatas($metadatas)
    {
        unset($metadatas['__id__']); // remove the hidden "new Metadata" row
        $this->_metadatas = [];
        foreach ($metadatas as $key => $metadata) {
            if (is_array($metadata)) {
                $this->_metadatas[$key] = $this->getMetadata($key);
                $this->_metadatas[$key]->setAttributes($metadata);
            } elseif ($metadata instanceof Metadata) {
                $this->_metadatas[$metadata->id] = $metadata;
            }
        }
    }

    /**
     * @param ActiveForm $form
     * @return mixed
     */
    public function errorSummary($form)
    {
        $errorLists = [];
        foreach ($this->getAllModels() as $id => $model) {
            $errorList = $form->errorSummary($model, [
                'header' => '<p>Please fix the following errors for <b>' . $id . '</b></p>',
            ]);
            $errorList = str_replace('<li></li>', '', $errorList); // remove the empty error
            $errorLists[] = $errorList;
        }
        return implode('', $errorLists);
    }

    /**
     * @return ActiveRecord[]
     */
    private function getAllModels()
    {
        $models = [
            //'form' => $this,
            'status' => $this->status,
        ];
        foreach ($this->metadatas as $id => $metadata) {
            $models['Metadata.' . $id] = $this->metadatas[$id];
        }
        return $models;
    }
}