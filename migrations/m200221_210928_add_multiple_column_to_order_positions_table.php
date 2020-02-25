<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%order_positions}}`.
 */
class m200221_210928_add_multiple_column_to_order_positions_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%order_positions}}', 'multiple', $this->integer()->notNull()->defaultValue(1)->after('amount'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%order_positions}}', 'multiple');
    }
}
