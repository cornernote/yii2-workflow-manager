<?php

namespace cornernote\workflow\manager\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "sw_status".
 *
 * @property integer $id
 * @property integer $workflow_id
 * @property string $name
 * @property string $label
 * @property integer $sort_order
 *
 * @property Workflow $workflow
 * @property Transition[] $startTransitions
 * @property Transition[] $endTransitions
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
            [['workflow_id', 'name'], 'required'],
            [['workflow_id', 'sort_order'], 'integer'],
            [['name', 'label'], 'string', 'max' => 255],
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
            'name' => Yii::t('app', 'Name'),
            'label' => Yii::t('app', 'Label'),
        ];
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
        return $this->hasMany(Transition::className(), ['start_status_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEndTransitions()
    {
        return $this->hasMany(Transition::className(), ['end_status_id' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        foreach ($this->startTransitions as $startTransition) {
            $startTransition->delete();
        }
        foreach ($this->endTransitions as $endTransition) {
            $endTransition->delete();
        }
        return parent::beforeDelete();
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        if (!$this->workflow->initial_status_id) {
            $this->workflow->initial_status_id = $this->id;
            $this->workflow->save(false, ['initial_status_id']);
        }
        parent::afterSave($insert, $changedAttributes);
    }

}
