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
                    'attribute' => 'price',
                    'label' => 'Цена',

                ],
                [
                    'attribute' => 'total',
                    'label' => 'Сумма',
                    'content' => function ($data) {
                        return $data->amount * $data->price;
                    },
                    'footer' => "<b>" . OrderPositions::getTotalPrice($positionProvider->models) . "</b>",
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{delete}',
                    'buttons' => [
                        'delete' => function ($url, $model, $key) {
                            return Html::a(
                                '<span class="glyphicon glyphicon-trash"></span>',
                                \yii\helpers\Url::to(['positions/delete', 'id' => $key, 'orderId' => $model->order_id]),
                                [
                                    'data-pjax' => '#model-grid',
                                    'title' => Yii::t('app', 'Delete')
                                ]
                            );
                        },
                    ]
                ],
            ],
            'showFooter' => true,
            'summary' => false,
        ]);
    }
}