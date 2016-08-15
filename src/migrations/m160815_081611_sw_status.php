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
    }

    public function safeDown()
    {
        $this->dropTable('{{%sw_status}}');
    }
    
}
