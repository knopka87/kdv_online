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
        $orderStatusDone = $order && in_array($order->status, Orders::statusDone(), true);
        if ($order &&
            (
                $isAdmin ||
                !$orderStatusDone
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
        elseif ($orderStatusDone) {
            Yii::$app->session->setFlash('danger', 'Удаление невозможно! Заказ заблокирован для изменений!');
        }

        Yii::$app->response->redirect(['orders/view', 'id' => $orderId]);
    }

}
