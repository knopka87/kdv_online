<?php

namespace app\models\query;

use app\models\UserBalance;
use app\models\UserBalanceLog;

/**
 * This is the ActiveQuery class for [[UserBalanceLog]].
 *
 * @see UserBalanceLog
 */
class UserBalanceLogQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return UserBalanceLog[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return UserBalanceLog|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function donate() {
        $this->andWhere(['type' => UserBalance::TYPE_DONATE]);
        return $this;
    }

    public function deposit() {
        $this->andWhere(['type' => UserBalance::TYPE_DEPOSIT]);
        return $this;
    }

    public function writeOff() {
        $this->andWhere(['type' => UserBalance::TYPE_WRITE_OFF]);
        return $this;
    }
}
