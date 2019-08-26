<?php

namespace app\models\query;

use app\models\UserBalance;

/**
 * This is the ActiveQuery class for [[UserBalance]].
 *
 * @see UserBalance
 */
class UserBalanceQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return UserBalance[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return UserBalance|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
