<?php

use app\models\OrderPositions;
use yii\bootstrap\Html;
use yii\grid\GridView;
?>
    <h1>Персональная статистика по заказам</h1>
    <p class="alert alert-info">Ты толстеешь, а кошелёк худеет! Посмотри на итоги..</p>
<?php
$columns = [
    [
        'attribute'=>'order_id',
        'label'=>'Заказ',
        'content'=>function($data) {
            return Html::a(
                'Заказ №'.$data->order_id,
                \yii\helpers\Url::to(['orders/view', 'id' => $data->order_id])
            );
        },
        'footer' => '<b>Итого</b>'
    ],
    [
        'attribute' => 'price',
        'label' => 'Сумма заказа',
        'content'=>function($data) {
            return OrderPositions::getDischangeHTML($data->price, 2);
        },
        'footer' => "<b>" . OrderPositions::getDischangeHTML($totalStat['price']) . "</b>",
    ],
    [
        'attribute' => 'amount',
        'label' => 'Кол-во позиций',
        'footer' => "<b>" . OrderPositions::getDischangeHTML($totalStat['amount']) . "</b>",
    ],
    [
        'attribute' => 'weight',
        'label' => 'Вес заказа, г',
        'content'=>function($data) {
            return OrderPositions::getDischangeHTML($data->weight, 2);
        },
        'footer' => "<b>" . OrderPositions::getDischangeHTML($totalStat['weight']) . "</b>",
    ],
    [
        'attribute' => 'protein',
        'label' => 'Белки, г',
        'content'=>function($data) {
            return OrderPositions::getDischangeHTML($data->protein, 2);
        },
        'footer' => "<b>" . OrderPositions::getDischangeHTML($totalStat['protein']) . "</b>",
    ],
    [
        'attribute' => 'fat',
        'label' => 'Жиры, г',
        'content'=>function($data) {
            return OrderPositions::getDischangeHTML($data->fat, 2);
        },
        'footer' => "<b>" . OrderPositions::getDischangeHTML($totalStat['fat']) . "</b>",
    ],
    [
        'attribute' => 'carbon',
        'label' => 'Углеводы, г',
        'content'=>function($data) {
            return OrderPositions::getDischangeHTML($data->carbon, 2);
        },
        'footer' => "<b>" . OrderPositions::getDischangeHTML($totalStat['carbon']) . "</b>",
    ],
    [
        'attribute' => 'kcal',
        'label' => 'Ккал',
        'content'=>function($data) {
            return OrderPositions::getDischangeHTML($data->kcal, 2);
        },
        'footer' => "<b>" . OrderPositions::getDischangeHTML($totalStat['kcal']) . "</b>",
    ],
];

echo GridView::widget([
    'dataProvider' => $statByOrderProvider,
    'columns' => $columns,
    'pager' => [
        'hideOnSinglePage' => true,
    ],
    'layout'=>"{items}\n{pager}",
    'showFooter' => true,
    'summary' => false,
]);
