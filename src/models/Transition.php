<?php

namespace cornernote\workflow\manager\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "sw_transition".
 *
 * @property integer $workflow_id
 * @property integer $start_status_id
 * @property integer $end_status_id
 *
 * @property Status $endStatus
 * @property Status $startStatus
 */
class Transition extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%sw_transition}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['workflow_id', 'start_status_id', 'end_status_id'], 'required'],
            [['workflow_id', 'start_status_id', 'end_status_id'], 'string', 'max' => 32],
            [['start_status_id'], 'exist', 'skipOnError' => true, 'targetClass' => Status::className(), 'targetAttribute' => ['start_status_id' => 'id']],
            [['end_status_id'], 'exist', 'skipOnError' => true, 'targetClass' => Status::className(), 'targetAttribute' => ['end_status_id' => 'id']]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'start_status_id' => Yii::t('app', 'Start Status'),
            'end_status_id' => Yii::t('app', 'End Status'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEndStatus()
    {
        return $this->hasOne(Status::className(), ['id' => 'end_status_id'])->andWhere(['workflow_id' => $this->workflow_id]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStartStatus()
    {
        return $this->hasOne(Status::className(), ['id' => 'start_status_id'])->andWhere(['workflow_id' => $this->workflow_id]);
    }
}
