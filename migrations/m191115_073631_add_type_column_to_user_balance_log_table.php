<?php

use app\models\UserBalance;
use yii\db\Migration;

/**
 * Handles adding columns to table `{{%user_balance_log}}`.
 */
class m191115_073631_add_type_column_to_user_balance_log_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user_balance_log}}', 'type', $this->integer());

        $this->createIndex('u_user_bl_type', '{{%user_balance_log}}', ['type']);

        $this->update('{{%user_balance_log}}', ['type' => UserBalance::TYPE_DEPOSIT], 'sum > 0');
        $this->update('{{%user_balance_log}}', ['type' => UserBalance::TYPE_WRITE_OFF], 'order_id > 0 AND sum < 0');
        $this->update('{{%user_balance_log}}', ['type' => UserBalance::TYPE_DONATE], 'order_id = 0 AND sum < 0');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%user_balance_log}}', 'type');
    }
}
