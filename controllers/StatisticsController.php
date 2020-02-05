<?php

namespace app\controllers;

use app\models\OrderPositions;
use app\models\Orders;
use app\models\UserBalance;
use app\models\Users;
use yii\data\ActiveDataProvider;

class StatisticsController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $userList = Users::find()->select(['username', 'id'])->asArray()->all();
        foreach ($userList as $user) {
            $users[$user['id']] = $user['username'];
        }

        return $this->render('index',
            [
                'users' => $users,
                'writeOffList' => UserBalance::topBalanceList('writeOff'),
                'countPositionsListByUser' => OrderPositions::topCountPositionsList(),
                'donateList' => UserBalance::topBalanceList('donate'),
                'weightListByUser' => OrderPositions::topWeightList(),
                'weightListByOrder' => Orders::getTopWeightOrder(),
                'countPositionsListByOrder' => Orders::getTopCountPositions(),
                'totalPriceListByOrder' => Orders::getTopTotalPriceOrder(),
                'countUsersListByOrder' => Orders::getTopCountUsers(),
                'popularPositions' => OrderPositions::getPopularPositions(),
                'topAmountPositions' => OrderPositions::getTopAmountPositions()
            ]);
    }

    public function actionPersonal() {

        if (\Yii::$app->user->isGuest || (int)\Yii::$app->user->id <= 0) {
            \Yii::$app->response->redirect(['site/login']);
        }

        $orderListQuuery = OrderPositions::find()
            ->addSelect(
                [
                    'order_id',
                    'SUM(amount) as amount',
                    'SUM(price*amount) as price',
                    'SUM(weight*amount) as weight',
                    'SUM(protein*amount*weight/100) as protein',
                    'SUM(fat*amount*weight/100) as fat',
                    'SUM(carbon*amount*weight/100) as carbon',
                    'SUM(kcal*amount*weight/100) as kcal'
                ]
            )
            ->andWhere(
            [
                'user_id' => \Yii::$app->user->id,
            ]
            )
            ->innerJoinWith([
                'order' => function ($query) {
                    $query->andWhere(['orders.status' => Orders::STATUS_PAYED]);
                }
            ])
            ->addGroupBy('order_id')
            ->orderBy('order_id DESC')
        ;
        $orderList = $orderListQuuery->all();
        $totalStat = [];
        foreach ($orderList as $order) {
            $totalStat['price'] += $order->price;
            $totalStat['amount'] += $order->amount;
            $totalStat['weight'] += $order->weight;
            $totalStat['protein'] += $order->protein;
            $totalStat['fat'] += $order->fat;
            $totalStat['carbon'] += $order->carbon;
            $totalStat['kcal'] += $order->kcal;
        }

        $dataProvider = new ActiveDataProvider(
            [
                'query' => $orderListQuuery,
                'pagination' => [
                    'pageSize' => 100
                ],
                'sort' => false,
            ]
        );

        return $this->render('personal',
            [
                'statByOrderProvider' => $dataProvider,
                'totalStat' => $totalStat
            ]
        );
    }
}
