<?php

use yii\db\Migration;

class m160815_081613_sw_workflow extends Migration
{

    public function safeUp()
    {
        $this->createTable('{{%sw_workflow}}', [
            'id' => $this->string(32)->notNull(),
            'initial_status_id' => $this->string(32)->null()->defaultValue(null),
            'PRIMARY KEY (id)',
        ], 'ENGINE=InnoDB');
        $this->createIndex('initial_status_id', '{{%sw_workflow}}', 'initial_status_id');
    }

    public function safeDown()
    {
        $this->dropIndex('initial_status_id', '{{%sw_workflow}}');
        $this->dropTable('{{%sw_workflow}}');
    }
    
}
