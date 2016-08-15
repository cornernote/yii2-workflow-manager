<?php

use yii\db\Migration;

class m160815_081614_Relations extends Migration
{

    public function safeUp()
    {
        $this->addForeignKey('fk_sw_status_workflow_id', '{{%sw_status}}', 'workflow_id', 'sw_workflow', 'id');
        $this->addForeignKey('fk_sw_transition_workflow_id', '{{%sw_transition}}', 'workflow_id', 'sw_workflow', 'id');
        $this->addForeignKey('fk_sw_transition_start_status_id', '{{%sw_transition}}', 'start_status_id', 'sw_status', 'id');
        $this->addForeignKey('fk_sw_transition_end_status_id', '{{%sw_transition}}', 'end_status_id', 'sw_status', 'id');
        $this->addForeignKey('fk_sw_workflow_initial_status_id', '{{%sw_workflow}}', 'initial_status_id', 'sw_status', 'id');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_sw_status_workflow_id', '{{%sw_status}}');
        $this->dropForeignKey('fk_sw_transition_workflow_id', '{{%sw_transition}}');
        $this->dropForeignKey('fk_sw_transition_start_status_id', '{{%sw_transition}}');
        $this->dropForeignKey('fk_sw_transition_end_status_id', '{{%sw_transition}}');
        $this->dropForeignKey('fk_sw_workflow_initial_status_id', '{{%sw_workflow}}');
    }
}
