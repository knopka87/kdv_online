<?php

use app\models\Orders;
use yii\db\Migration;

/**
 * Class m190816_125723_order_positions
 */
class m190816_125723_orders extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%orders}}', [
            'id' => $this->primaryKey(),
            'status' => $this->smallInteger()->notNull()->defaultValue(Orders::STATUS_ACTIVE),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ]);

        $this->createTable('{{%order_positions}}', [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'kdv_url' => $this->string()->notNull(),
            'amount' => $this->smallInteger()->notNull(),
            'price' => $this->float(),
            'caption' => $this->string(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ]);

        $this->createIndex('u_kdv_url', '{{%order_positions}}', ['order_id', 'user_id', 'kdv_url'], true);

        $this->addForeignKey('fk_order', '{{%order_positions}}', 'order_id', '{{%orders}}', 'id', 'cascade', 'cascade');
        $this->addForeignKey('fk_user', '{{%order_positions}}', 'user_id', '{{%users}}', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_order', '{{%order_positions}}');
        $this->dropTable('{{%order_positions}}');
        $this->dropTable('{{%order_orders}}');

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190816_125723_order_positions cannot be reverted.\n";

        return false;
    }
    */
}
