<?php

namespace app\controllers;

use app\models\OrderPositions;
use app\models\Orders;
use Yii;

class PositionsController extends \yii\web\Controller
{
    public function actionDelete($orderId, $id)
    {
        $order = Orders::findIdentity($id);
        if ($order && !in_array($order->status, Orders::statusDone())) {
            OrderPositions::deleteAll(
                [
                    'id' => $id,
                    'order_id' => $orderId,
                    'user_id' => Yii::$app->user->id,
                ]
            );
        }

        Yii::$app->response->redirect(['orders/view', 'id' => $orderId]);
    }

}
