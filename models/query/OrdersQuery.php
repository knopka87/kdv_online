<?php

namespace app\models\query;

use app\models\Orders;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[Order]].
 *
 * @see Orders
 */
class OrdersQuery extends ActiveQuery
{

    /**
     * {@inheritdoc}
     *
     * @return Orders[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     *
     * @return Orders|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @return $this
     */
    public function active() {

        $statusList = [
            Orders::STATUS_ACTIVE
        ];
        $statusDoneList = Orders::statusDone();
        $statusList = array_merge($statusList, $statusDoneList);
        $this->andWhere(['or', ['status' => $statusList]]);
        return $this;
    }
}
