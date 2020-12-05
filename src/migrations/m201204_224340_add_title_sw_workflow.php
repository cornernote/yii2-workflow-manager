<?php
use yii\db\Schema;
use yii\db\Migration;

/**
 * Class m201204_224340_add_title_sw_workflow
 */
class m201204_224340_add_title_sw_workflow extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        echo "m201204_224340_add_title_sw_workflow.\n";
        $this->addColumn('{{%sw_workflow}}'
            , 'title'
            , $this->varchar(32)->defaultValue(null));

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m201204_224340_add_title_sw_workflow to be reverted.\n";


        if($this->dropColumn('{{%sw_status}}', 'title'))
            echo "m201204_224340_add_title_sw_workflow cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m201204_224340_add_title_sw_workflow cannot be reverted.\n";

        return false;
    }
    */
}
