<?php

namespace cornernote\workflow\manager\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "sw_workflow".
 *
 * @property string $id
 * @property string $initial_status_id
 *
 * @property Status[] $statuses
 * @property Status $initialStatus
 * @property Transition[] $transitions
 * @property Metadata[] $metadatas
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
     * @return \yii\db\ActiveQuery
     */
    public function getTransitions()
    {
        return $this->hasMany(Transition::className(), ['workflow_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMetadatas()
    {
        return $this->hasMany(Metadata::className(), ['workflow_id' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (!$insert && $this->id != $this->oldAttributes['id']) {
            $id = $this->id;
            $this->id = $this->oldAttributes['id'];
            foreach ($this->statuses as $status) {
                $status->workflow_id = $id;
                $status->save(false, ['workflow_id']);
            }
            foreach ($this->transitions as $transition) {
                $transition->workflow_id = $id;
                $transition->save(false, ['workflow_id']);
            }
            foreach ($this->metadatas as $metadata) {
                $metadata->workflow_id = $id;
                $metadata->save(false, ['workflow_id']);
            }
            $this->id = $id;
        }
        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        $this->initial_status_id = null;
        $this->save(false, ['initial_status_id']);
        foreach ($this->statuses as $status) {
            $status->delete();
        }
        return parent::beforeDelete();
    }

    /**
     * @return string
     */
    public function getColor()
    {
        $string = $this->id;
        $darker = 1.3;
        $rgb = substr(dechex(crc32(str_repeat($string, 10) . md5($string))), 0, 6);
        list($R16, $G16, $B16) = str_split($rgb, 2);
        $R = sprintf("%02X", floor(hexdec($R16) / $darker));
        $G = sprintf("%02X", floor(hexdec($G16) / $darker));
        $B = sprintf("%02X", floor(hexdec($B16) / $darker));
        return '#' . $R . $G . $B;
    }

}
