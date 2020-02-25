<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%kdv_categories}}`.
 */
class m190926_071432_create_kdv_categories_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%kdv_categories}}', [
            'id' => $this->primaryKey(),
            'kdv_id' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'url' => $this->string()->notNull()->unique(),
            'image_src' => $this->string(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%kdv_categories}}');
    }
}
