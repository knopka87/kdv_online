<?php

namespace app\controllers;

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

            /** @var OrderPositions $position */
            $position = OrderPositions::find()->andWhere($filterList)->one();
            if ($position) {
                $position->deletePosition();
            }
        }
        elseif (in_array($order->status, Orders::statusDone(), true)) {
            Yii::$app->session->setFlash('danger', 'Удаление невозможно! Заказ заблокирован для изменений!');
        }

        Yii::$app->response->redirect(['orders/view', 'id' => $orderId]);
    }

}
