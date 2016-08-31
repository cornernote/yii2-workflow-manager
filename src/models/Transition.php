<?php

namespace cornernote\workflow\manager\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "sw_transition".
 *
 * @property string $workflow_id
 * @property string $start_status_id
 * @property string $end_status_id
 *
 * @property string $startName
 * @property string $endName
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
     * @return string
     */
    public function getStartName()
    {
        return $this->startStatus->getName();
    }

    /**
     * @return string
     */
    public function getEndName()
    {
        return $this->endStatus->getName();
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
