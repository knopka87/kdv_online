<?php

use yii\db\Expression;
use yii\db\Migration;

/**
 * Handles adding columns to table `{{%order_positions}}`.
 */
class m200221_220334_add_kdv_price_column_to_order_positions_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%order_positions}}', 'kdv_price', $this->float()->notNull()->after('price'));
        $this->update('{{%order_positions}}', ['kdv_price' => new Expression('[[price]]')]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%order_positions}}', 'kdv_price');
    }
}
