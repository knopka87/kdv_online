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
                    'pageSize' => 20
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
            UserBalance::depositBalanse(
                $userBalanceLog->sum,
                $userBalanceLog->user_id,
                $userBalanceLog->order_id
            );
        }

        return $this->render('deposite', [
            'users' => $this->getAllUsers(),
            'orders' => $this->getAllOrders(),
            'balanceList' => $this->getAllBalance(),
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

    protected function getAllUsers() {

        $userList = Users::find()->active()->addSelect(['id', 'username'])->all();
        return ArrayHelper::map($userList,'id','username');
    }

    protected function getAllOrders() {

        $orderList = Orders::find()
            ->addSelect(['id'])
            ->orderBy('created_at DESC')
            ->all();

        return ArrayHelper::map($orderList,'id','id');
    }

    protected function getAllBalance() {
        return ArrayHelper::map(UserBalance::find()->addSelect(['user_id', 'balance'])->orderBy('balance ASC')->all(), 'user_id', 'balance');
    }

}
