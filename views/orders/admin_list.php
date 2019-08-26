<?php

use app\models\OrderPositions;
use yii\grid\GridView;
use yii\helpers\Html;

?>
<h1>Заказ № <?=$order->id?> от <?=\Yii::$app->formatter->asDate($order->created_at, 'php:d.m.Y')?></h1>
<br>
<?= GridView::widget([
    'dataProvider' => $positionProvider,
    'columns' => [
        [
            'attribute' => 'kdv_url',
            'label' => 'Товар',
            'content' => function($data) {
                return Html::a($data->caption, $data->kdv_url, ['target' => '_blank']);
            },
            'footer' => '<b>Итого:</b>',
        ],
        [
            'attribute' => 'amount',
            'label' => 'Кол-во'
        ],
        [
            'attribute' => 'price',
            'label' => 'Цена',

        ],
        [
            'attribute' => 'total',
            'label' => 'Сумма',
            'content'=>function($data) {
                return $data->amount*$data->price;
            },
            'footer' => '<b>' .OrderPositions::getTotalBalance($positionProvider->models). '</b>',
        ],
    ],
    'showFooter' => true,
    'summary' => false,
]); ?>