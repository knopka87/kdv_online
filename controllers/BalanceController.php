<?php

namespace app\controllers;

use app\models\OrderPositions;
use app\models\Orders;
use app\models\UserBalance;
use app\models\UserBalanceLog;
use app\models\Users;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

class BalanceController extends \yii\web\Controller
{
    public function beforeAction($action) {

        if (Yii::$app->user->isGuest) {
            Yii::$app->response->redirect(['site/login']);
        }
        return true;
    }

    public function actionIndex()
    {
        $balanceQuery = UserBalanceLog::find()
            ->andWhere(['user_id' =>\Yii::$app->user->id])
            ->orderBy('created_at DESC')
        ;

        $balanceProvider = new ActiveDataProvider(
            [
                'query' => $balanceQuery,
                'pagination' => [
                    'pageSize' => 10
                ],
                'sort' => false,
            ]
        );

        return $this->render('index', ['balanceProvider' => $balanceProvider]);
    }

    public function actionDeposite() {

        if (!Yii::$app->user->identity->isAdmin()) {
            Yii::$app->response->redirect(['balance/index']);
        }

        //PositionsUpdate
        if (Yii::$app->request->isPost) {
            $userBalanceLog = new UserBalanceLog();
            $userBalanceLog->load(Yii::$app->request->post());
            UserBalance::changeBalance(
                $userBalanceLog->sum,
                $userBalanceLog->user_id,
                $userBalanceLog->order_id,
                UserBalance::TYPE_DEPOSIT,
                $userBalanceLog->comment
            );
        }

        return $this->render('deposite', [
            'users' => $this->getAllActiveUsers(),
            'orders' => $this->getAllOrders(),
            'balanceList' => $this->getAllBalance(),
            'model'=> new UserBalanceLog()
        ]);
    }

    public function actionDonate() {

        if (Yii::$app->request->isPost) {
            $userBalanceLog = new UserBalanceLog();
            $userBalanceLog->load(Yii::$app->request->post());
            UserBalance::changeBalance(
                $userBalanceLog->sum,
                \Yii::$app->user->id,
                0,
                UserBalance::TYPE_DONATE
            );
            UserBalance::changeBalance(
                $userBalanceLog->sum,
                1,
                0,
                UserBalance::TYPE_DEPOSIT,
                'Вам пожертвование от '.Yii::$app->user->identity->username
            );
        }

        return $this->render('donate', [
            'model'=> new UserBalanceLog()
        ]);
    }

    // TODO функция в разработке
    public function actionRefreshBalance() {

        if (!Yii::$app->user->identity->isAdmin()) {
            Yii::$app->response->redirect(['balance/index']);
        }

        //PositionsUpdate
        if (Yii::$app->request->isPost) {
            $userBalanceLog = new UserBalanceLog();
            $userBalanceLog->load(Yii::$app->request->post());

        }
    }

    protected function getAllActiveUsers() {

        $userList = Users::find()->active()->addSelect(['id', 'username'])->all();
        return ArrayHelper::map($userList,'id','username');
    }

    protected function getAllOrders() {

        $orderList = Orders::find()
            ->addSelect(['id as value', 'id as label' ])
            ->orderBy('created_at DESC')
            ->asArray()
            ->all();

        return $orderList;
    }

    protected function getAllBalance() {
        return ArrayHelper::map(
            UserBalance::find()
                ->addSelect(['user_id', 'balance'])
                ->innerJoinWith(['user' => function($query) {
                    $query->andWhere(['users.active' => Users::STATUS_ACTIVE]);
                }])
                ->orderBy('balance ASC')
                ->all(),
            'user_id',
            'balance'
        );
    }

}
