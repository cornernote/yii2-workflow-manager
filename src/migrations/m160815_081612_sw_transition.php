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
        $this->createIndex('workflow_start_status_id', '{{%sw_transition}}', ['workflow_id', 'start_status_id']);
        $this->createIndex('workflow_end_status_id', '{{%sw_transition}}', ['workflow_id', 'end_status_id']);
    }

    public function safeDown()
    {
        $this->dropIndex('workflow_start_status_id', '{{%sw_transition}}');
        $this->dropIndex('workflow_end_status_id', '{{%sw_transition}}');
        $this->dropTable('{{%sw_transition}}');
    }
    
}
