<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%order_positions}}`.
 */
class m200204_121319_add_protein_column_fat_column_carbon_column_kcal_column_to_order_positions_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%order_positions}}', 'protein', $this->float());
        $this->addColumn('{{%order_positions}}', 'fat', $this->float());
        $this->addColumn('{{%order_positions}}', 'carbon', $this->float());
        $this->addColumn('{{%order_positions}}', 'kcal', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%order_positions}}', 'protein');
        $this->dropColumn('{{%order_positions}}', 'fat');
        $this->dropColumn('{{%order_positions}}', 'carbon');
        $this->dropColumn('{{%order_positions}}', 'kcal');
    }
}
