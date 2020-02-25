<?php
/* @var $this yii\web\View
 * @var $order \app\models\Orders
 * @var \yii\data\ActiveDataProvider $positionProvider
 */

use app\models\OrderPositions;
use app\models\OrdersUsers;
use app\widgets\Alert;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

$isAdmin = !Yii::$app->user->isGuest && Yii::$app->user->identity->isAdmin();
?>
<h1>Заказ № <?=$order->id?> от <?=\Yii::$app->formatter->asDate($order->created_at, 'php:d.m.Y')?></h1>
    <br>
<?php $form = \yii\bootstrap\ActiveForm::begin([
    'layout' => 'inline'
])?>
<?= $form->field($ordersUsersModel, 'status')->hiddenInput(['value' => OrdersUsers::STATUS_START]);?>&nbsp;
    <button type="submit" class="btn btn-primary">
        <?=$ordersUsersStatus=='new'?'Участвую в заказе!':'Хочу изменить заказ!!'?></button>
<?php \yii\bootstrap\ActiveForm::end();?>
<?php
if (!empty($whoIsProcessing)) {
    echo '<h2>Ещё заказывают</h2>';
    /** @var OrdersUsers[] $whoIsProcessing */
    foreach ($whoIsProcessing as $ordersUsers) {
        echo $ordersUsers->user->username. ' ';
    }
}
if (!empty($countUsers)) {
    echo '<br>Всего участников: '.$countUsers;
}
?>
    <br><br>
<?php
if ($ordersUsersStatus == 'done') {
    if (!$positionProvider) {
        echo 'Список товаров пуст';
    } else {
        echo GridView::widget([
            'dataProvider' => $positionProvider,
            'columns' => [
                [
                    'attribute' => 'kdv_url',
                    'label' => 'Товар',
                    'content' => function ($data) {
                        return Html::a($data->caption, $data->kdv_url, ['target' => '_blank']);
                    },
                    'footer' => '<b>Итого:</b>',
                ],
                [
                    'attribute' => 'amount',
                    'label' => 'Кол-во',
                ],
                [
                    'attribute' => $isAdmin?'kdv_price':'price',
                    'label' => 'Цена',

                ],
                [
                    'attribute' => 'total',
                    'label' => 'Сумма',
                    'content' => function ($data) {
                        $isAdmin = !Yii::$app->user->isGuest && Yii::$app->user->identity->isAdmin();
                        if ($isAdmin) {
                            return $data['amount']*$data['kdv_price'];
                        }
                        return $data['amount']*$data['price'];
                    },
                    'footer' => "<b>" . OrderPositions::getTotalPrice($positionProvider->models) . "</b>",
                ],
            ],
            'showFooter' => true,
            'summary' => false,
        ]);
    }
}
?>
    <br>
<h2>Итоговый заказ</h2>
<?= GridView::widget([
    'dataProvider' => $totalPositionProvider,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        [
            'attribute' => 'kdv_url',
            'label' => 'Товар',
            'content' => function($data) {
                return Html::a($data['caption'], $data['kdv_url'], ['target' => '_blank']);
            },
            'footer' => '<b>Итого:</b>',
        ],
        [
            'attribute' => 'amount',
            'label' => 'Кол-во',
            'content' => function($data) {
                return round($data['amount'], 2);
            }
        ],
        [
            'attribute' => 'multiple',
            'label' => 'Упаковка, шт'
        ],
        [
            'attribute' => $isAdmin?'kdv_price':'price',
            'label' => 'Цена',

        ],
        [
            'attribute' => 'total',
            'label' => 'Сумма',
            'content'=>function($data) {
                $isAdmin = !Yii::$app->user->isGuest && Yii::$app->user->identity->isAdmin();
                if ($isAdmin) {
                    return round($data['amount']*$data['kdv_price'], 2);
                }
                return round($data['amount']*$data['price'], 2);
            },
            'footer' => '<b>' .OrderPositions::getTotalPrice($totalPositionProvider->models). '</b>',
        ],
        [
            'attribute' => 'users',
            'label' => 'Пользователи',
            'content'=>function($data) {
                return $data['username'];
            },
        ],
    ],
    'showFooter' => true,
    'summary' => false,
    'rowOptions' => function ($model, $key, $index, $grid)
    {
        if($model['multiple'] !== 1 && ($model['amount'] % $model['multiple']) !== 0) {
            return ['style' => 'background-color:#ee9999;'];
        }
    },
]);
