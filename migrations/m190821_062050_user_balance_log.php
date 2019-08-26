<?php

use yii\db\Migration;

/**
 * Class m190821_062050_user_balance_log
 */
class m190821_062050_user_balance_log extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->createTable('{{%user_balance_log}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'order_id' => $this->integer()->notNull()->defaultValue(0),
            'comment' => $this->string(),
            'sum' => $this->float()->notNull()->defaultValue(0),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ]);

        $this->createIndex('u_user_bl', '{{%user_balance_log}}', ['user_id']);
        $this->addForeignKey('fk_user_bl', '{{%user_balance_log}}', 'user_id', '{{%users}}', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('u_user_bl', '{{%user_balance_log}}');
        $this->dropForeignKey('fk_user_bl', '{{%user_balance_log}}');
        $this->dropTable('{{%user_balance}}');

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190821_062050_user_balance_log cannot be reverted.\n";

        return false;
    }
    */
}
