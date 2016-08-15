<?php

namespace cornernote\workflow\manager\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "sw_workflow".
 *
 * @property integer $id
 * @property integer $initial_status_id
 *
 * @property Status[] $statuses
 * @property Status $initialStatus
 */
class Workflow extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%sw_workflow}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'initial_status_id'], 'string', 'max' => 32],
            [['initial_status_id'], 'exist', 'skipOnError' => true, 'targetClass' => Status::className(), 'targetAttribute' => ['initial_status_id' => 'id']]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'initial_status_id' => Yii::t('app', 'Initial Status'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStatuses()
    {
        return $this->hasMany(Status::className(), ['workflow_id' => 'id'])->orderBy(['sort_order' => SORT_ASC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInitialStatus()
    {
        return $this->hasOne(Status::className(), ['id' => 'initial_status_id']);
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        $this->initial_status_id = null;
        $this->save(false,['initial_status_id']);
        foreach ($this->statuses as $status) {
            $status->delete();
        }
        return parent::beforeDelete();
    }
}
