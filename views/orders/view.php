<?php
/* @var $this yii\web\View */

use app\models\OrderPositions;
use yii\grid\GridView;
use yii\helpers\Html;

?>
<h1>Заказ № <?=$order->id?> от <?=\Yii::$app->formatter->asDate($order->created_at, 'php:d.m.Y')?></h1>


<?php
/*
$positions = $positions->all();
foreach ($positions as $position) {
    var_dump($position->user->username);
}*/


$columns = [
    [
        'attribute'=>'kdv_url',
        'label'=>'Ссылка на KDV',
        'content'=>function($data) {
            $content = Html::a(
                $data->caption?:$data->kdv_url,
                \yii\helpers\Url::to([$data->kdv_url], '')
            );
            if ($data->user->id === Yii::$app->user->id) {
                $content = '<b>' . $content . '</b>';
            }
            return $content;
        },
        'footer' => '<b>Итого:</b>',
    ],
    [
        'attribute'=>'amount',
        'label'=>'Кол-во',
        'content'=>function($data) {
            $content = $data->amount;
            if ($data->user->id === Yii::$app->user->id) {
                $content = '<b>' . $content . '</b>';
            }
            return $content;
        }
    ],
    [
        'attribute'=>'price',
        'label'=>'Цена',
        'content'=>function($data) {
            $content = $data->price;
            if ($data->user->id === Yii::$app->user->id) {
                $content = '<b>' . $content . '</b>';
            }
            return $content;
        },
    ],
    [
        'attribute' => 'total',
        'label' => 'Сумма',
        'content' => function ($data) {
            $content = $data->amount * $data->price;
            if ($data->user->id === Yii::$app->user->id) {
                $content = '<b>' . $content . '</b>';
            }
            return $content;
        },
        'footer' => "<b>" . OrderPositions::getTotalBalance($positionProvider->models) . "</b>",
    ],
    [
        'attribute' => 'username',
        'label' => 'Пользователь',
        'content'=>function($data) {
            $content = $data->user->username;
            if ($data->user->id === Yii::$app->user->id) {
                $content = '<b>' . $content . '</b>';
            }
            return $content;
        }
    ]
];

echo GridView::widget([
    'dataProvider' => $positionProvider,
    'columns' => $columns,
    'pager' => [
        'hideOnSinglePage' => true,
    ],
    'layout'=>"{items}\n{pager}",
    'showFooter' => true,
    'summary' => false,
]);