<?php

namespace app\controllers;

use app\models\OrderPositions;
use app\models\Orders;
use app\models\UserBalance;
use app\models\UserBalanceLog;
use app\models\Users;
use Yii;
use yii\data\ActiveDataProvider;

class OrdersController extends \yii\web\Controller
{

    public function beforeAction($action) {

        if (Yii::$app->user->isGuest) {
            Yii::$app->response->redirect(['site/login']);
        }
        return true;
    }
    public function actionAdd()
    {
        $todayOrder = Orders::findTodayOrder();
        if (!$todayOrder) {
            $order = new Orders();
            $order->status = Orders::STATUS_ACTIVE;
            if ($order->save()) {
                $todayOrder = $order;
            }
        }

        Yii::$app->response->redirect(['orders/view', 'id' => $todayOrder->id]);
    }

    public function actionDelete($id)
    {
        $order = Orders::findIdentity($id);
        $order->status = Orders::STATUS_NOT_ACTIVE;
        $order->save();

        Yii::$app->response->redirect(['orders/list']);
    }

    public function actionView($id)
    {
        //PositionsUpdate
        if (Yii::$app->request->isPost) {
            $orderPosition = new OrderPositions();
            $orderPosition->order_id = $id;
            $orderPosition->user_id = Yii::$app->user->id;
            if ($orderPosition->load(Yii::$app->request->post())) {
                $orderPosition->addPosition();
            }
        }

        $order = Orders::findIdentity($id);
        $positionModel = new OrderPositions();

        if ($order->status == Orders::STATUS_ACTIVE) {
            $view = 'edit';
            $positionsQuery = OrderPositions::find()
                ->andWhere(['order_id' => $id,'user_id' => Yii::$app->user->id]);

        }
        else {
            $view = 'view';
            $positionsQuery = OrderPositions::find()
                ->addSelect(['*', 'IF(user_id = '.Yii::$app->user->id.', 0, 1) as sort_user'])
                ->andWhere(['order_id' => $id])
                ->orderBy(['sort_user' => SORT_ASC, 'user_id' => SORT_ASC]);
        }

        $dataProvider = new ActiveDataProvider(
            [
                'query' => $positionsQuery,
                'pagination' => [
                    'pageSize' => 100
                ],
                'sort' => false,
            ]
        );

        $userList = Users::find()->select(['username', 'id'])->asArray()->all();
        foreach ($userList as $user) {
            $users[$user['id']] = $user['username'];
        }

        return $this->render(
            $view,
            [
                'order' => $order,
                'users' => $users,
                'positionProvider' => $dataProvider,
                'positionModel' => $positionModel,
                'writeOffList' => UserBalance::topBalanceList('writeOff', $id),
                'countPositionsList' => OrderPositions::topCountPositionsList($id),
                'weightList' => OrderPositions::topWeightList($id)
            ]
        );
    }

    public function actionAdminList($id) {

        $order = Orders::findIdentity($id);
        $positionModel = new OrderPositions();

        $positionsQuery = OrderPositions::find()
            ->andWhere(['order_id' => $id])
            ->groupBy('kdv_url')
            ->addSelect('order_id, kdv_url, price, caption')
            ->addSelect('SUM([[amount]]) AS amount')
            ->orderBy(['amount' => SORT_DESC])
        ;

        $dataProvider = new ActiveDataProvider(
            [
                'query' => $positionsQuery,
                'pagination' => [
                    'pageSize' => 100
                ],
                'sort' => false,
            ]
        );

        return $this->render(
            'admin_list',
            ['order' => $order, 'positionProvider' => $dataProvider, 'positionModel' => $positionModel]
        );
    }

    public function actionList()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Orders::find()->active()->orderBy('created_at desc'),
            'pagination' => [
                'pageSize' => 20
            ],
        ]);
        return $this->render('list', ['dataProvider' => $dataProvider]);
    }

    public function actionClose($id) {
        $order = Orders::findIdentity($id);
        $order->status = Orders::STATUS_CLOSE;
        $order->update();

        $sumListByUser = [];
        $positions = $order->getOrderPositions()->all();
        foreach ($positions as $position) {
            $sumListByUser[$position->user_id] += $position->amount * $position->price;
        }

        foreach ($sumListByUser as $userId => $sum) {
                // списание средств со счёта
            UserBalance::changeBalance(
                $sum,
                $userId,
                $id,
                UserBalance::TYPE_WRITE_OFF
            );
        }

        Yii::$app->response->redirect(['orders/list']);
    }

    public function actionOpen($id) {

        $order = Orders::findIdentity($id);
        if ($order->status == Orders::STATUS_CLOSE) {

            $order->status = Orders::STATUS_ACTIVE;
            $order->update();

            $balanceLogs = UserBalanceLog::find()
                ->andWhere(['order_id' => $id])
                ->andFilterCompare('sum', '<0')
                ->all();

            foreach ($balanceLogs as $balanceLog) {
                $balance = UserBalance::find()->andWhere(['user_id' => $balanceLog->user_id])->one();
                $balance->balance -= round($balanceLog->sum, 2);
                $balance->save();

                $balanceLog->delete();
            }
        }
        Yii::$app->response->redirect(['orders/list']);
    }
}
