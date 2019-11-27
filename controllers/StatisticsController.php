<?php

namespace app\controllers;

use app\models\OrderPositions;
use app\models\Orders;
use app\models\UserBalance;
use app\models\Users;

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
}
