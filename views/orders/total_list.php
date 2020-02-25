<?php

use app\models\OrderPositions;
use yii\grid\GridView;
use yii\helpers\Html;

$isAdmin = !Yii::$app->user->isGuest && Yii::$app->user->identity->isAdmin();
?>
<h1>Заказ № <?=$order->id?> от <?=\Yii::$app->formatter->asDate($order->created_at, 'php:d.m.Y')?></h1>
<br>
<?= GridView::widget([
    'dataProvider' => $totalPositionProvider,
    'columns' => [
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
            'label' => 'Кол-во'
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
                    return $data['amount']*$data['kdv_price'];
                }
                return $data['amount']*$data['price'];
            },
            'footer' => '<b>' .OrderPositions::getTotalPrice($positionProvider->models). '</b>',
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
        if($model['multiple'] > 1 && $model['amount'] !== $model['multiple']) {
            return ['style' => 'background-color:#ee9999;'];
        }
    },
]); ?>
