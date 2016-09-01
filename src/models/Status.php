<?php

namespace cornernote\workflow\manager\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Inflector;

/**
 * This is the model class for table "sw_status".
 *
 * @property string $id
 * @property string $workflow_id
 * @property string $label
 * @property integer $sort_order
 * 
 * @property string $name
 *
 * @property Workflow $workflow
 * @property Transition[] $startTransitions
 * @property Transition[] $endTransitions
 * @property Metadata[] $metadatas
 */
class Status extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%sw_status}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'workflow_id'], 'required'],
            [['sort_order'], 'integer'],
            [['id', 'workflow_id'], 'string', 'max' => 32],
            [['label'], 'string', 'max' => 64],
            [['workflow_id'], 'exist', 'skipOnError' => true, 'targetClass' => Workflow::className(), 'targetAttribute' => ['workflow_id' => 'id']]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'workflow_id' => Yii::t('app', 'Workflow'),
            'label' => Yii::t('app', 'Label'),
        ];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->label ? $this->label : Inflector::camel2words($this->id);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWorkflow()
    {
        return $this->hasOne(Workflow::className(), ['id' => 'workflow_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStartTransitions()
    {
        return $this->hasMany(Transition::className(), ['start_status_id' => 'id'])
            ->andWhere(['{{%sw_transition}}.workflow_id' => $this->workflow_id])
            ->leftJoin('{{%sw_status}}', '{{%sw_status}}.id = {{%sw_transition}}.end_status_id AND {{%sw_status}}.workflow_id = :workflow_id', [
                ':workflow_id' => $this->workflow_id,
            ])
            ->orderBy(['{{%sw_status}}.sort_order' => SORT_ASC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEndTransitions()
    {
        return $this->hasMany(Transition::className(), ['end_status_id' => 'id'])
            ->andWhere(['{{%sw_transition}}.workflow_id' => $this->workflow_id])
            ->leftJoin('{{%sw_status}}', '{{%sw_status}}.id = {{%sw_transition}}.start_status_id AND {{%sw_status}}.workflow_id = :workflow_id', [
                ':workflow_id' => $this->workflow_id,
            ])
            ->orderBy(['{{%sw_status}}.sort_order' => SORT_ASC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMetadatas()
    {
        return $this->hasMany(Metadata::className(), ['status_id' => 'id'])->andWhere(['workflow_id' => $this->workflow_id]);
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ($insert) {
            if ($this->sort_order === null) {
                $lowest = static::find()->andWhere(['workflow_id' => $this->workflow_id])->orderBy(['sort_order' => SORT_DESC])->one();
                $this->sort_order = $lowest ? $lowest->sort_order + 1 : 1;
            }
        }
        if (!$insert && $this->id != $this->oldAttributes['id']) {
            $id = $this->id;
            $this->id = $this->oldAttributes['id'];
            if ($this->workflow->initial_status_id == $this->id) {
                $this->workflow->initial_status_id = $id;
                $this->workflow->save(false, ['initial_status_id']);
            }
            foreach ($this->startTransitions as $startTransition) {
                $startTransition->start_status_id = $id;
                $startTransition->save(false, ['start_status_id']);
            }
            foreach ($this->endTransitions as $endTransition) {
                $endTransition->end_status_id = $id;
                $endTransition->save(false, ['end_status_id']);
            }
            foreach ($this->metadatas as $metadata) {
                $metadata->delete();
            }
            $this->id = $id;
        }
        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($this->workflow && !$this->workflow->initial_status_id) {
            $this->workflow->initial_status_id = $this->id;
            $this->workflow->save(false, ['initial_status_id']);
        }
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        foreach ($this->metadatas as $metadata) {
            $metadata->delete();
        }
        foreach ($this->startTransitions as $startTransition) {
            $startTransition->delete();
        }
        foreach ($this->endTransitions as $endTransition) {
            $endTransition->delete();
        }
        return parent::beforeDelete();
    }

}
