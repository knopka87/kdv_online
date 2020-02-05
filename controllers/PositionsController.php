<?php

namespace app\controllers;

use app\models\Notification;
use app\models\OrderPositions;
use app\models\Orders;
use Yii;

class PositionsController extends \yii\web\Controller
{
    public function actionDelete($orderId, $id)
    {
        $order = Orders::findIdentity($orderId);
        $isAdmin = Yii::$app->user->identity->isAdmin();
        if ($order &&
            (
                $isAdmin ||
                !in_array($order->status, Orders::statusDone(), true)
            )
        ) {
            $filterList = [
                'id' => $id,
                'order_id' => $orderId,
            ];
            if (!$isAdmin) {
                $filterList['user_id'] = Yii::$app->user->id;
            }

            $position = OrderPositions::findIdentity($id);

            OrderPositions::deleteAll($filterList);

            if ($isAdmin && $position->user_id !== Yii::$app->user->id) {
                $notification = new Notification();
                $notification->title = 'Удалена позиция из заказа №' . $orderId;
                $notification->body = '"' . $position->caption . '" - нет в наличии на КДВ';
                $notification->clickAction = 'https://' . $_SERVER['HTTP_HOST'] .
                    \yii\helpers\Url::to(['orders/view', 'id' => $orderId]);
                $notification->send([$position->user_id]);
            }
        }
        elseif (in_array($order->status, Orders::statusDone(), true)) {
            \Yii::$app->session->setFlash('danger', 'Удаление невозможно! Заказ заблокирован для изменений!');
        }

        Yii::$app->response->redirect(['orders/view', 'id' => $orderId]);
    }

}
