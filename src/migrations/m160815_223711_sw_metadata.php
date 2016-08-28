<?php

use yii\db\Schema;
use yii\db\Migration;

class m160815_223711_sw_metadata extends Migration
{

    public function safeUp()
    {
        $this->createTable('{{%sw_metadata}}', [
            'workflow_id' => $this->string(32)->notNull(),
            'status_id' => $this->string(32)->notNull(),
            'key' => $this->string(64)->notNull(),
            'value' => $this->string(255)->null()->defaultValue(null),
        ], 'ENGINE=InnoDB');
        $this->createIndex('workflow_status_id', '{{%sw_metadata}}', ['workflow_id', 'status_id', 'key'], true);
    }

    public function safeDown()
    {
        $this->dropIndex('workflow_status_id', '{{%sw_metadata}}');
        $this->dropTable('{{%sw_metadata}}');
    }

}
