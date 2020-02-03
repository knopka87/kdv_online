<?php

use app\models\Tokens;
use yii\db\Migration;

/**
 * Handles the creation of table `{{%tokens}}`.
 */
class m191212_100249_create_tokens_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%tokens}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'token' => $this->string()->notNull()->unique(),
            'status' => $this->integer()->notNull()->defaultValue(Tokens::STATUS_ACTIVE)
        ]);

        $this->createIndex(
            'idx-tokens-username',
            '{{%tokens}}',
            'user_id'
        );

        $this->addForeignKey('fk_tokens_users', '{{%tokens}}', 'user_id', '{{%users}}', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-tokens-username', '{{%tokens}}');
        $this->dropForeignKey('fk_tokens_users', '{{%tokens}}');
        $this->dropTable('{{%tokens}}');
    }
}
