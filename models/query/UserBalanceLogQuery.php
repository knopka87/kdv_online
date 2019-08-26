<?php

namespace app\models\query;

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
}
