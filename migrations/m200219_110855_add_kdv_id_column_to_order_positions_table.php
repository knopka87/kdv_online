<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%order_positions}}`.
 */
class m200219_110855_add_kdv_id_column_to_order_positions_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%order_positions}}', 'kdv_id', $this->integer()->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%order_positions}}', 'kdv_id');
    }
}
