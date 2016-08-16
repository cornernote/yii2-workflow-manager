<?php

use yii\db\Migration;

class m160815_081611_sw_status extends Migration
{

    public function safeUp()
    {
        $this->createTable('{{%sw_status}}', [
            'id' => $this->string(32)->notNull(),
            'workflow_id' => $this->string(32)->notNull(),
            'label' => $this->string(64)->null()->defaultValue(null),
            'sort_order' => $this->integer(11)->null()->defaultValue(null),
            'PRIMARY KEY (id, workflow_id)',
        ], 'ENGINE=InnoDB');
        $this->createIndex('workflow_id', '{{%sw_status}}', 'workflow_id');
    }

    public function safeDown()
    {
        $this->dropIndex('workflow_id', '{{%sw_status}}');
        $this->dropTable('{{%sw_status}}');
    }
    
}
