<?php

namespace app\models\query;

use app\models\OrdersUsers;

/**
 * This is the ActiveQuery class for [[OrdersUsers]].
 *
 * @see OrdersUsers
 */
class OrdersUsersQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return OrdersUsers[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return OrdersUsers|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
