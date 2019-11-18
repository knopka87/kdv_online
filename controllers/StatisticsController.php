<?php

namespace app\controllers;

use app\models\OrderPositions;
use app\models\UserBalanceLog;
use app\models\Users;

class StatisticsController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $userList = Users::find()->select(['username', 'id'])->asArray()->all();
        foreach ($userList as $user) {
            $users[$user['id']] = $user['username'];
        }

        $writeOffList = $this->topBalanceList('writeOff');
        $donateList = $this->topBalanceList('donate');
        $countPositionsList = $this->topCountPositionsList();
        $wightList = $this->topWeightList();
        return $this->render('index',
            [
                'users' => $users,
                'writeOffList' => $writeOffList,
                'countPositionsList' => $countPositionsList,
                'donateList' => $donateList,
                'weightList' => $wightList
            ]);
    }

    /**
     * 3 лучших донатчика/транжиры
     *
     * @param string $topType
     * @return array
     */
    private function topBalanceList($topType) {

        if (!in_array($topType, ['writeOff', 'donate'])) {
            return [];
        }

        return UserBalanceLog::find()
            ->select(['user_id','ABS(SUM(`sum`)) as sum'])
            ->groupBy('user_id')
            ->orderBy('sum DESC')
            ->andWhere('user_id <> 1')
            ->limit(3)
            ->$topType()
            ->asArray()
            ->all();
    }

    private function topCountPositionsList() {

        return OrderPositions::find()
            ->select(['SUM(amount) as count_pos', 'user_id'])
            ->andWhere('user_id <> 1')
            ->groupBy('user_id')
            ->orderBy('count_pos DESC')
            ->limit(3)
            ->asArray()
            ->all();
    }

    private function topWeightList() {
        return OrderPositions::find()
            ->select(['SUM(amount*weight) as count_pos', 'user_id'])
            ->andWhere('user_id <> 1')
            ->groupBy('user_id')
            ->orderBy('count_pos DESC')
            ->limit(3)
            ->asArray()
            ->all();
    }
}
