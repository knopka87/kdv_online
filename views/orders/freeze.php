<?php
/* @var $this yii\web\View
 * @var $order \app\models\Orders
 * @var \yii\data\ActiveDataProvider $positionProvider
 */

use app\models\OrderPositions;
use app\models\OrdersUsers;
use app\models\Tools;
use yii\grid\GridView;
use yii\helpers\Html;

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
if ($ordersUsersStatus === 'done') {
    if (!$positionProvider) {
        echo 'Список товаров пуст';
    } else {
        echo Html::tag('h2', 'Мой заказ');
        $columns = [
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
                'content' => function ($data) {
                    return Tools::priceFormat($data->price);
                }

            ],
            [
                'attribute' => 'total',
                'label' => 'Сумма',
                'content' => function ($data) {
                    return Tools::priceFormat($data->amount*$data->price);
                },
                'footer' => '<b>' . OrderPositions::getTotalPrice($positionProvider->models) . '</b>',
            ],
        ];

        if ($isAdmin) {
            $columns[] = [
                'attribute' => 'price',
                'label' => 'Цена КДВ',
                'content' => function($data) {
                    return Tools::priceFormat($data->kdv_price);
                }

            ];
            $columns[] = [
                'attribute' => 'total',
                'label' => 'Сумма КДВ',
                'content' => function ($data) {
                    return Tools::priceFormat($data->amount*$data->kdv_price);
                },
                'footer' => '<b>' . OrderPositions::getTotalPrice($positionProvider->models, true) . '</b>',
            ];
        }

        echo GridView::widget([
            'dataProvider' => $positionProvider,
            'columns' => $columns,
            'showFooter' => true,
            'summary' => false,
        ]);
    }
}
?>
    <br>
<h2>Итоговый заказ</h2>
<?php
$columns = [
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
        'attribute' => 'price',
        'label' => 'Цена',
        'content' => function ($data) {
            return Tools::priceFormat($data['price']);
        }

    ],
    [
        'attribute' => 'total',
        'label' => 'Сумма',
        'content'=>function($data) {
            return Tools::priceFormat($data['amount']*$data['price']);
        },
        'footer' => '<b>' .OrderPositions::getTotalPrice($totalPositionProvider->models). '</b>',
    ]
];
if ($isAdmin) {
    $columns[] = [
        'attribute' => 'kdv_price',
        'label' => 'Цена КДВ',
        'content' => function ($data) {
            return Tools::priceFormat($data['kdv_price']);
        }

    ];
    $columns[] = [
        'attribute' => 'total',
        'label' => 'Сумма КДВ',
        'content'=>function($data) {
            return Tools::priceFormat($data['amount']*$data['kdv_price']);
        },
        'footer' => '<b>' .OrderPositions::getTotalPrice($totalPositionProvider->models, true). '</b>',
    ];
}
$columns[] = [
    'attribute' => 'users',
    'label' => 'Пользователи',
    'content'=>function($data) {
        return $data['username'];
    },
];
echo GridView::widget([
    'dataProvider' => $totalPositionProvider,
    'columns' => $columns,
    'showFooter' => true,
    'summary' => false,
    'rowOptions' => function ($model, $key, $index, $grid)
    {
        if($model['multiple'] !== 1 && ($model['amount'] % $model['multiple']) !== 0) {
            return ['style' => 'background-color:#ee9999;'];
        }
    },
]);
