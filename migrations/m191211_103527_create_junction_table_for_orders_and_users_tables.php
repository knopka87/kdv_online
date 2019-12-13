<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%orders_users}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%orders}}`
 * - `{{%users}}`
 */
class m191211_103527_create_junction_table_for_orders_and_users_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%orders_users}}', [
            'orders_id' => $this->integer(),
            'users_id' => $this->integer(),
            'status' => $this->integer()->defaultValue(0),
            'PRIMARY KEY(orders_id, users_id)',
        ]);

        // creates index for column `orders_id`
        $this->createIndex(
            '{{%idx-orders_users-orders_id}}',
            '{{%orders_users}}',
            'orders_id'
        );

        // add foreign key for table `{{%orders}}`
        $this->addForeignKey(
            '{{%fk-orders_users-orders_id}}',
            '{{%orders_users}}',
            'orders_id',
            '{{%orders}}',
            'id',
            'CASCADE'
        );

        // creates index for column `users_id`
        $this->createIndex(
            '{{%idx-orders_users-users_id}}',
            '{{%orders_users}}',
            'users_id'
        );

        // add foreign key for table `{{%users}}`
        $this->addForeignKey(
            '{{%fk-orders_users-users_id}}',
            '{{%orders_users}}',
            'users_id',
            '{{%users}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%orders}}`
        $this->dropForeignKey(
            '{{%fk-orders_users-orders_id}}',
            '{{%orders_users}}'
        );

        // drops index for column `orders_id`
        $this->dropIndex(
            '{{%idx-orders_users-orders_id}}',
            '{{%orders_users}}'
        );

        // drops foreign key for table `{{%users}}`
        $this->dropForeignKey(
            '{{%fk-orders_users-users_id}}',
            '{{%orders_users}}'
        );

        // drops index for column `users_id`
        $this->dropIndex(
            '{{%idx-orders_users-users_id}}',
            '{{%orders_users}}'
        );

        $this->dropTable('{{%orders_users}}');
    }
}
