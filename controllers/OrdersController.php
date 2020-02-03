<?php

namespace app\controllers;

use app\models\Notification;
use app\models\OrderPositions;
use app\models\Orders;
use app\models\OrdersUsers;
use app\models\Tools;
use app\models\UserBalance;
use app\models\UserBalanceLog;
use app\models\Users;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;

class OrdersController extends \yii\web\Controller
{

    public function beforeAction($action)
    {

        if (Yii::$app->user->isGuest || (int)Yii::$app->user->id <= 0) {
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

                $notification = new Notification();
                $notification->title = 'Новый заказ!';
                $notification->body = 'Для перехода к заказу нажмите на данное сообщение';
                $notification->clickAction = 'https://' . $_SERVER['HTTP_HOST'].
                    \yii\helpers\Url::to(['orders/view', 'id' => $todayOrder->id]);
                $notification->send();
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
        $ordersUsers = new OrdersUsers();

        if (Yii::$app->request->isPost) {
            $postKeys = array_keys(Yii::$app->request->post());
            $typeUpdate = next($postKeys);

            switch ($typeUpdate) {
                case 'OrderPositions':
                    $orderPosition = new OrderPositions();
                    $orderPosition->order_id = $id;
                    $orderPosition->user_id = Yii::$app->user->id;
                    if ($orderPosition->load(Yii::$app->request->post())) {
                        $orderPosition->addPosition();
                    }
                    break;
                case 'OrdersUsers':
                    $findOrdersUsers = OrdersUsers::find()->andWhere([
                        'orders_id' => $id,
                        'users_id' => Yii::$app->user->id
                    ])->one();
                    if (!$findOrdersUsers) {
                        $ordersUsers->orders_id = $id;
                        $ordersUsers->users_id = Yii::$app->user->id;
                    }
                    else {
                        $ordersUsers = $findOrdersUsers;
                    }
                    if ($ordersUsers->load(Yii::$app->request->post())) {

                        if (!$findOrdersUsers) {
                            $ordersUsers->insert();
                        }
                        else {
                            $ordersUsers->update();
                        }
                    }
                    break;
            }
        }

        $order = Orders::findIdentity($id);

        if (!$order) {
            Yii::$app->response->redirect(['orders/list']);
            return '';
        }

        $positionModel = new OrderPositions();

        if ($order->status == Orders::STATUS_ACTIVE) {

            $myOrdersUsers = OrdersUsers::find()->andWhere(['orders_id' => $id, 'users_id' => Yii::$app->user->id])->addSelect(['status'])->one();

            if (!$myOrdersUsers) {
                $view = 'freeze';
                $ordersUsersStatus = 'new';
            }
            elseif ($myOrdersUsers->status == OrdersUsers::STATUS_DONE) {
                $view = 'freeze';
                $ordersUsersStatus = 'done';
            }
            else {
                $view = 'edit';
                $ordersUsersStatus = 'process';
            }

            $positionsQuery = OrderPositions::find()
                ->andWhere(['order_id' => $id,'user_id' => Yii::$app->user->id]);

        }
        elseif (in_array($order->status, Orders::statusDone())) {
            $view = 'view';
            $ordersUsersStatus = 'close';
            $positionsQuery = OrderPositions::find()
                ->addSelect(['*', 'IF(user_id = '.Yii::$app->user->id.', 0, 1) as sort_user'])
                ->andWhere(['order_id' => $id])
                ->orderBy(['sort_user' => SORT_ASC, 'user_id' => SORT_ASC]);
        }
        else {
            return ''; // заказ не активен
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

        $params = [
            'order' => $order,
            'users' => $users,
            'positionProvider' => $dataProvider,
            'positionModel' => $positionModel,
            'ordersUsersModel' => $ordersUsers,
            'ordersUsersStatus' => $ordersUsersStatus
        ];

        if (in_array($order->status, Orders::statusDone())) {
            $params['writeOffList'] = UserBalance::topBalanceList('writeOff', $id);
            $params['countPositionsList'] = OrderPositions::topCountPositionsList($id);
            $params['weightList'] = OrderPositions::topWeightList($id);
        }
        elseif ($order->status == Orders::STATUS_ACTIVE) {
            $params['topUsedPosition'] = OrderPositions::getTopUsedPosition(Yii::$app->user->id);
            $params['whoIsProcessing'] = $order->whoIsProcessing();
        }

        return $this->render($view, $params);
    }

    public function actionAdminList($id) {

        if (!Yii::$app->user->identity->isAdmin()) {
            Yii::$app->response->redirect(['order/list']);
        }

        $order = Orders::findIdentity($id);

        $userList = Users::find()->select(['username', 'id'])->asArray()->all();
        foreach ($userList as $user) {
            $users[$user['id']] = $user['username'];
        }

        $positionModel = new OrderPositions();

        $positionsQuery = OrderPositions::find()
            ->andWhere(['order_id' => $id])
            ->groupBy('kdv_url')
            ->addSelect('order_id, kdv_url, price, caption, user_id')
            ->addSelect('SUM([[amount]]) AS amount')
            ->joinWith('user')
            ->addSelect('GROUP_CONCAT(DISTINCT users.username SEPARATOR \', \') AS `username`')
            ->orderBy(['amount' => SORT_DESC])
            ->asArray();
        ;

        $dataProvider = new ArrayDataProvider(
            [
                'allModels' => $positionsQuery->all(),
                'pagination' => [
                    'pageSize' => 100
                ],
                'sort' => false,
            ]
        );

        return $this->render(
            'admin_list',
            [
				'order' => $order,
				'users' => $users,
				'positionProvider' => $dataProvider,
				'positionModel' => $positionModel
			]
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

    public function actionBlock($id) {

        if (!Yii::$app->user->identity->isAdmin()) {
            Yii::$app->response->redirect(['order/list']);
        }

        $order = Orders::findIdentity($id);
        if ($order) {
            $order->status = Orders::STATUS_BLOCK;
            $order->update();

            $userList = [];
            $positions = $order->getOrderPositions()->all();
            if ($positions) {
                foreach ($positions as $position) {
                    $userList[] = $position->user_id;
                }

                // отправка уведомлений только тем кто участвует в заказе
                $notification = new Notification();
                $notification->title = 'Заказ №'.$id.' заблокирован';
                $notification->body = 'Изменения в заказе больше не принимаются!';
                $notification->clickAction = 'https://' . $_SERVER['HTTP_HOST'] .
                    \yii\helpers\Url::to(['balance/index']);
                $notification->send($userList);
            }
        }
        Yii::$app->response->redirect(['orders/list']);
    }
    public function actionPayOrder($id) {

        if (!Yii::$app->user->identity->isAdmin()) {
            Yii::$app->response->redirect(['order/list']);
        }

        $order = Orders::findIdentity($id);
        if ($order) {

            $userList = [];
            $positions = $order->getOrderPositions()->all();
            foreach ($positions as $position) {

                $userList[] = $position->user_id;

                UserBalance::changeBalance(
                    $position->amount * $position->price,
                    $position->user_id,
                    $id,
                    UserBalance::TYPE_WRITE_OFF
                );
            }

            $order->status = Orders::STATUS_PAYED;
            $order->update();

            // отправка уведомлений только тем кто участвует в заказе
            $notification = new Notification();
            $notification->title = 'Изменение баланса';
            $notification->body = 'Произошло снятие средств за заказ №'.$id.'. Нажмите на сообщение для просмотра ' .
                'статистики по балансу с дальнейшим переходом на оплату.';
            $notification->clickAction = 'https://' . $_SERVER['HTTP_HOST'] .
                \yii\helpers\Url::to(['balance/index']);
            $notification->send($userList);
        }

        Yii::$app->response->redirect(['orders/list']);
    }

    public function actionOpen($id) {

        $order = Orders::findIdentity($id);
        if ($order && in_array($order->status, Orders::statusDone())) {

            if ($order->status === Orders::STATUS_PAYED) {

                // отменяем ранее сделанное списание средств за заказ

                $balanceLogs = UserBalanceLog::find()
                    ->andWhere(['order_id' => $id, 'type' => UserBalance::TYPE_WRITE_OFF])
                    ->all();

                foreach ($balanceLogs as $balanceLog) {
                    $balance = UserBalance::find()->andWhere(['user_id' => $balanceLog->user_id])->one();
                    $balance->balance -= round($balanceLog->sum, 2);
                    $balance->save();

                    $balanceLog->delete();
                }
            }

            $order->status = Orders::STATUS_ACTIVE;
            $order->update();
        }
        Yii::$app->response->redirect(['orders/list']);
    }
}
