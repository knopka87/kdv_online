<?php

use app\models\Users;
use yii\db\Migration;

class m140703_123000_user extends Migration
{
    /**
     * @return bool|void
     */
    public function safeUp()
    {
        $this->createTable('{{%users}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string(32),
            'passwordHash' => $this->string()->notNull(),
            'authKey' => $this->string(32)->notNull(),
            'accessToken' => $this->string(40)->notNull(),            
            'role' => $this->string(5)->notNull()->defaultValue(Users::ROLE_USER),
            'active' => $this->smallInteger()->notNull()->defaultValue(Users::STATUS_ACTIVE),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ]);
    }

    /**
     * @return bool|void
     */
    public function safeDown()
    {
        $this->dropTable('{{%users}}');

    }
}
