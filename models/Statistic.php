<?php

namespace app\models;


class Statistic
{
    private static $noStat = [
        'all' => [
            [
                'user_id' => 24,
            ]
        ],
        'donate' => [
            [
                'user_balance_log.id' => [187,130,126,244,278]
            ]
        ]
    ];

    public static function topCountPositionsList($orderId = 0)
    {

        return OrderPositions::find()
            ->select(['SUM(amount) as count_pos', 'user_id', 'order_id'])
            ->andWhere(
                self::andWhereStatistics() .
                ($orderId > 0 ? ' AND order_id = ' . $orderId : '')
            )
            ->innerJoinWith([
                'order' => function ($query) {
                    return $query->andWhere(['orders.status' => Orders::STATUS_PAYED]);
                }
            ])
            ->groupBy('user_id')
            ->orderBy('count_pos DESC')
            ->limit(3)
            ->asArray()
            ->all();
    }

    public static function topWeightList($orderId = 0)
    {

        return OrderPositions::find()
            ->select(['SUM(amount*weight) as count_pos', 'user_id', 'order_id'])
            ->andWhere(
                self::andWhereStatistics() .
                ($orderId > 0 ? ' AND order_id = ' . $orderId : '')
            )
            ->innerJoinWith([
                'order' => function ($query) {
                    $query->andWhere(['orders.status' => Orders::STATUS_PAYED]);
                }
            ])
            ->groupBy('user_id')
            ->orderBy('count_pos DESC')
            ->limit(3)
            ->asArray()
            ->all();
    }

    public static function andWhereStatistics($type = 'all')
    {
        switch ($type) {
            case 'donate' :
                break;
            default:
                $type = 'all';
                break;
        }
        $where = '';
        foreach (self::$noStat[$type] as $whereList) {
            $whereOr = [];
            foreach ($whereList as $field => $value) {
                if (is_array($value)) {
                    $whereOr[] = "{$field} NOT IN ('" . implode("', '", $value) . "')";
                } else {
                    $whereOr[] = "{$field} <> '{$value}'";
                }
            }
            $where .= ' AND (' . implode(' OR ', $whereOr) . ')';
        }
        return substr($where, 4);
    }

    public static function getTopUsedPosition($userId) {

        $userId = (int)$userId;
        if ($userId <= 0) {
            return [];
        }
        return OrderPositions::find()
            ->addSelect(['*', 'COUNT(order_positions.id) as count'])
            ->andWhere(
                ['user_id' => $userId]
            )
            /*->innerJoinWith(['order' => function ($query) {
				$query->andWhere(['orders.status' => Orders::STATUS_PAYED]);
				}
			])*/
            ->andHaving('`count` > 2')
            ->groupBy('kdv_url')
            ->limit(10)
            ->orderBy('count DESC')
            ->asArray()
            ->all();
    }

    public static function getPopularPositions() {

        return OrderPositions::find()
            ->addSelect(['*', 'COUNT(order_positions.id) as count'])
            ->addGroupBy('kdv_url')
            ->orderBy('count DESC')
            ->asArray()
            ->limit(10)
            ->all();
    }

    public static function getTopAmountPositions() {

        return OrderPositions::find()
            ->addSelect(['*', 'SUM(amount) as count'])
            ->addGroupBy('kdv_url')
            ->orderBy('count DESC')
            ->limit(10)
            ->asArray()
            ->all();
    }


    /**
     * @return array
     */
    public static function getTopWeightOrder() {

        return OrderPositions::find()
            ->addSelect(['*', 'SUM(weight*amount) as weight'])
            ->addGroupBy('order_id')
            ->innerJoinWith(['order' => function ($query) {
                $query->andWhere(['orders.status' => Orders::STATUS_PAYED]);
            }
            ])
            ->orderBy('weight DESC')
            ->limit(3)
            ->asArray()
            ->all();
    }

    /**
     * @return array
     */
    public static function getTopTotalPriceOrder() {

        return OrderPositions::find()
            ->addSelect(['*', 'SUM(amount*price) as sum'])
            ->addGroupBy('order_id')
            ->innerJoinWith(['order' => function ($query) {
                return $query->andWhere(['orders.status' => Orders::STATUS_PAYED]);
            }
            ])
            ->orderBy('sum DESC')
            ->limit(3)
            ->asArray()
            ->all();
    }

    /**
     * @return array
     */
    public static function getTopCountPositions() {

        return OrderPositions::find()
            ->addSelect(['*', 'SUM(amount) as sum'])
            ->addGroupBy('order_id')
            ->innerJoinWith(['order' => function ($query) {
                $query->andWhere(['orders.status' => Orders::STATUS_PAYED]);
            }
            ])
            ->orderBy('sum DESC')
            ->limit(3)
            ->asArray()
            ->all();
    }

    /**
     * @return array
     */
    public static function getTopCountUsers() {

        return OrderPositions::find()
            ->addSelect(['order_id', 'COUNT(DISTINCT user_id) as sum'])
            ->innerJoinWith(['order' => function ($query) {
                $query->andWhere(['orders.status' => Orders::STATUS_PAYED]);
            }
            ])
            ->addGroupBy('order_id')
            ->orderBy('sum DESC')
            ->limit(3)
            ->asArray()
            ->all();
    }

    /**
     * 3 лучших донатчика/транжиры
     *
     * @param string $topType
     * @param int $orderId
     * @return array
     */
    public static function topBalanceList($topType, $orderId = 0) {

        if (!in_array($topType, ['writeOff', 'donate'])) {
            return [];
        }

        return UserBalanceLog::find()
            ->select(['user_id','ABS(SUM(`sum`)) as sum'])
            ->andWhere(
                self::andWhereStatistics($topType).
                ($orderId>0 ? ' AND order_id = '.$orderId : '')
            )
            ->innerJoinWith(['user' => function($query) {
                $query->andWhere(['users.active' => Users::STATUS_ACTIVE]);
            }])
            ->$topType()
            ->groupBy('user_id')
            ->orderBy('sum DESC')
            ->limit(3)
            ->asArray()
            ->all();
    }
}
