<?php

use app\models\Orders;
use yii\db\Migration;

/**
 * Class m200131_135123_update_order_status
 */
class m200131_135123_update_order_status extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('{{%orders}}', ['status' => Orders::STATUS_PAYED], 'status = '. Orders::STATUS_BLOCK);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200131_135123_update_order_status cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200131_135123_update_order_status cannot be reverted.\n";

        return false;
    }
    */
}
