<?php

use yii\db\Migration;

class m160815_081612_sw_transition extends Migration
{

    public function safeUp()
    {
        $this->createTable('{{%sw_transition}}', [
            'workflow_id' => $this->string(32)->notNull(),
            'start_status_id' => $this->string(32)->notNull(),
            'end_status_id' => $this->string(32)->notNull(),
            'PRIMARY KEY (workflow_id, start_status_id, end_status_id)',
        ], 'ENGINE=InnoDB');
        $this->createIndex('workflow_id', '{{%sw_transition}}', 'workflow_id');
        $this->createIndex('start_status_id', '{{%sw_transition}}', 'start_status_id');
        $this->createIndex('end_status_id', '{{%sw_transition}}', 'end_status_id');
    }

    public function safeDown()
    {
        $this->dropIndex('workflow_id', '{{%sw_transition}}');
        $this->dropIndex('start_status_id', '{{%sw_transition}}');
        $this->dropIndex('end_status_id', '{{%sw_transition}}');
        $this->dropTable('{{%sw_transition}}');
    }
}
