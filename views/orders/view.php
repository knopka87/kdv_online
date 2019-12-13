<?php
/* @var $this yii\web\View */

use app\models\OrderPositions;
use app\models\Tools;
use yii\grid\GridView;
use yii\helpers\Html;

$this->registerCssFile('@web/css/style-75.css');
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
        'attribute'=>'weight',
        'label'=>'Вес, г.',
        'content'=>function($data) {
            $content = $data->weight;
            if ($data->user->id === Yii::$app->user->id) {
                $content = '<b>' . $content . '</b>';
            }
            return $content;
        },
        'footer' => "<b>" . OrderPositions::getDischangeHTML(OrderPositions::getTotalWeight($positionProvider->models)) . "</b>",
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
        },
        'footer' => "<b>" . Tools::pageTotal($positionProvider->models, 'amount') . "</b>",
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
        'footer' => "<b>" . OrderPositions::getTotalPrice($positionProvider->models) . "</b>",
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
?>
<h2>Доска почёта по заказу</h2>
<div class="row ui-75">
    <div class="col-md-4 col-sm-6 col-xs-6 col-mob">
        <div class="ui-item">
            <div class="bg-yellow text-center">
                <h2>Коллекционеры</h2>
                <small>Больше всех заказали единиц товара</small>
            </div>
            <ul class="clearfix">
                <?php foreach ($countPositionsList as $key => $countPositions):?>
                    <li>
                        <?=$users[$countPositions['user_id']]?>
                        <span><?=$countPositions['count_pos']?> шт.</span>
                    </li>
                <?php endforeach;?>
            </ul>
        </div>
    </div>
    <div class="col-md-4 col-sm-6 col-xs-6 col-mob">
        <div class="ui-item">
            <div class="bg-brown text-center">
                <h2>Тяжеловесы</h2>
                <small>Больше всех заказали по весу</small>
            </div>
            <ul class="clearfix">
                <?php foreach ($weightList as $key => $weight):?>
                    <li>
                        <?=$users[$weight['user_id']]?>
                        <span><?=OrderPositions::getDischangeHTML($weight['count_pos']/1000, 3)?> кг.</span>
                    </li>
                <?php endforeach;?>
            </ul>
        </div>
    </div>
    <div class="col-md-4 col-sm-6 col-xs-6 col-mob">
        <div class="ui-item">
            <div class="bg-green text-center">
                <h2>Транжиры</h2>
                <small>Потратил больше всех на заказ</small>
            </div>
            <ul class="clearfix">
                <?php foreach ($writeOffList as $key => $writeOff):?>
                    <li>
                        <?=$users[$writeOff['user_id']]?>
                        <span><?=OrderPositions::getDischangeHTML($writeOff['sum'], 2)?> р.</span>
                    </li>
                <?php endforeach;?>
            </ul>
        </div>
    </div>
</div>