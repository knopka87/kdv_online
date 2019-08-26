<?php

use yii\db\Migration;

/**
 * Class m190820_130451_user_balance
 */
class m190820_130451_user_balance extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->createTable('{{%user_balance}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'balance' => $this->float()->notNull()->defaultValue(0),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ]);

        $this->createIndex('u_user_b', '{{%user_balance}}', ['user_id'], true);
        $this->addForeignKey('fk_user_b', '{{%user_balance}}', 'user_id', '{{%users}}', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('u_user_b', '{{%user_balance}}');
        $this->dropForeignKey('fk_user_b', '{{%user_balance}}');
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
        echo "m190820_130451_user_balance cannot be reverted.\n";

        return false;
    }
    */
}
