<?php

namespace app\controllers;

use app\models\kdv\KdvBasket;
use app\models\Notification;
use app\models\OrderPositions;
use app\models\OrderPositionsSearch;
use app\models\Orders;
use app\models\OrdersUsers;
use app\models\Statistic;
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
            Yii::$app->user->loginRequired();
            return false;
        }
        return true;
    }
    public function actionAdd()
    {
        $todayOrder = Orders::findActiveOrder();
        if (!$todayOrder) {
            $order = new Orders();
            $order->status = Orders::STATUS_ACTIVE;
            if ($order->save()) {
                $todayOrder = $order;

                $kdvBasket = new KdvBasket();
                $kdvBasket->clearBasket();

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
            $post = Yii::$app->request->post();
            $postKeys = array_keys($post);

            foreach ($postKeys as $typeUpdate) {
                switch ($typeUpdate) {
                    case 'OrderPositions':
                        $orderPosition = new OrderPositions();
                        $orderPosition->order_id = $id;
                        $orderPosition->user_id = Yii::$app->user->id;
                        if ($orderPosition->load($post)) {
                            $orderPosition->addPosition();
                        }
                        if ($post['ajax']) {
                            return
                                Yii::$app->session->getFlash('danger', null, true)?:
                                    Yii::$app->session->getFlash('info', null, true)?:
                                        Yii::$app->session->getFlash('success', null, true);
                        }
                        break;
                    case 'OrdersUsers':
                        $findOrdersUsers = OrdersUsers::find()->andWhere([
                            'order_id' => $id,
                            'user_id' => Yii::$app->user->id
                        ])->one();
                        if (!$findOrdersUsers) {
                            $ordersUsers->order_id = $id;
                            $ordersUsers->user_id = Yii::$app->user->id;
                        } else {
                            $ordersUsers = $findOrdersUsers;
                        }
                        if ($ordersUsers->load(Yii::$app->request->post())) {

                            if (!$findOrdersUsers) {
                                $ordersUsers->insert();
                            } else {
                                $ordersUsers->update();
                            }
                        }
                        break;
                }
            }
        }

        $order = Orders::findIdentity($id);

        if (!$order) {
            Yii::$app->response->redirect(['orders/list']);
            return '';
        }

        $positionModel = new OrderPositions();

        if ($order->status == Orders::STATUS_ACTIVE) {

            $myOrdersUsers = OrdersUsers::find()->andWhere(['order_id' => $id, 'user_id' => Yii::$app->user->id])->addSelect(['status'])->one();

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

            $totalPositionsQuery = OrderPositions::find()
                ->andWhere(['order_id' => $id])
                ->groupBy('kdv_url')
                ->addSelect('order_id, kdv_url, price, kdv_price, caption, user_id, multiple')
                ->addSelect('SUM([[amount]]) AS amount')
                ->joinWith('user')
                ->addSelect(['GROUP_CONCAT(DISTINCT users.username,  :p1 , [[amount]],  :p2 SEPARATOR \', \') AS username'])
                ->addParams([':p1' => ' (', ':p2' => 'шт.)'])
                ->orderBy(['multiple' => SORT_DESC])
                ->asArray();
            ;

            $totalPositionProvider = new ArrayDataProvider(
                [
                    'allModels' => $totalPositionsQuery->all(),
                    'pagination' => [
                        'pageSize' => 100
                    ],
                    'sort' => false,
                ]
            );
        }
        elseif (in_array($order->status, Orders::statusDone())) {
            $view = 'view';
            $ordersUsersStatus = 'close';

            $searchModel = new OrderPositionsSearch();
            $viewTotalDataProvider = $searchModel->getTotalPositionProvider($id, Yii::$app->request->post());
        }
        else {
            return ''; // заказ не активен
        }

        $positionsQuery = OrderPositions::find()
            ->andWhere(['order_id' => $id,'user_id' => Yii::$app->user->id]);

        $dataProvider = new ActiveDataProvider(
            [
                'query' => $positionsQuery,
                'pagination' => [
                    'pageSize' => 100
                ],
                'sort' => false,
            ]
        );

        $users = [];
        $userList = OrderPositions::find()
            ->joinWith('user')
            ->select(['username', 'user_id'])
            ->andWhere(['order_id' =>  $id])
            ->asArray()->all();
        foreach ($userList as $user) {
            $users[$user['user_id']] = $user['username'];
        }

        $params = [
            'order' => $order,
            'users' => $users,
            'positionProvider' => $dataProvider,
            'positionModel' => $positionModel,
            'ordersUsersModel' => $ordersUsers,
            'ordersUsersStatus' => $ordersUsersStatus
        ];

        if ($order->status == Orders::STATUS_ACTIVE) {
            $params['totalPositionProvider'] = $totalPositionProvider;
            $params['topUsedPosition'] = Statistic::getTopUsedPosition(Yii::$app->user->id);
            $params['whoIsProcessing'] = $order->whoIsProcessing();
            $params['countUsers'] = OrdersUsers::find()->andWhere(['order_id' => $id])->count();
        }
        elseif (in_array($order->status, Orders::statusDone())) {
            $params['writeOffList'] = Statistic::topBalanceList('writeOff', $id);
            $params['countPositionsList'] = Statistic::topCountPositionsList($id);
            $params['weightList'] = Statistic::topWeightList($id);
            $params['viewTotalPositionsList'] = $viewTotalDataProvider;
            $params['searchModel'] = $searchModel;
        }

        return $this->render($view, $params);
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

            $kdvBasket = new KdvBasket();
            $message = $kdvBasket->sincBasket($id);
            Yii::$app->session->setFlash('info', $message);

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

        UserBalance::payOrder($id);

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
