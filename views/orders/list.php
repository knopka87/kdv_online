<?php
/* @var $this yii\web\View
 * @var $dataProvider \yii\data\ActiveDataProvider
 */

use app\models\Orders;
use app\models\Users;
use yii\grid\GridView;
use yii\helpers\Html;


$this->title = 'Список заказов';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-6">
        <h1><?= Html::encode($this->title) ?></h1>
    </div>
    <div class="col-md-6" style="margin-top: 20px;margin-bottom: 10px;">
        <a href="<?=\yii\helpers\Url::to(['statistics/personal'])?>" class="btn btn-primary" style="float: right">Персональная статистика</a>
    </div>
</div>


<?php

$columns = [
    [
        'attribute'=>'created_at',
        'label'=>'Заказы',
        'format'=>'date', // Доступные модификаторы - date:datetime:time
        'content'=>function($data) {
            $content = Html::a(
                    'Заказ №'.$data->id.' от ' . \Yii::$app->formatter->asDate($data->created_at, 'php:d.m.Y'),
                    \yii\helpers\Url::to(['orders/view', 'id' => $data->id])
            );

            if(!Yii::$app->user->isGuest && Yii::$app->user->identity->isAdmin()) {
                $content .= '&nbsp;&nbsp;'.Html::a(
                    '<span class="glyphicon glyphicon-list"></span>',
                    \yii\helpers\Url::to(['orders/admin-list', 'id' => $data->id]),
                    ['title' => 'Показать итоговый заказ']
                );
            }

            return $content;
        }
    ],
    [
        'attribute'=>'is_today',
        'label'=>'Статус',
        'content'=>function($data) {
            $adminContent = '';
            /** @var Orders $data */
            if ($data->status == Orders::STATUS_ACTIVE) {
                $content = 'В процессе..';
                if (!$data->isProcessing()) {
                    $adminContent = '&nbsp;&nbsp;'.Html::a(
                            '<span class="glyphicon glyphicon-ok"></span>',
                            \yii\helpers\Url::to(['orders/block', 'id' => $data->id]),
                            ['title' => 'Заблокировать заказ']
                        );
                }

            }
            elseif (in_array($data->status, Orders::statusDone(), true)) {

                switch ($data->status) {
                    case Orders::STATUS_BLOCK:
                        $content = 'Заблокирован';
                        break;
                    default:
                    case Orders::STATUS_PAYED:
                        $content = 'Завершён';
                        break;
                }

                $adminContent = '&nbsp;&nbsp;' . Html::a(
                        '<span class="glyphicon glyphicon-eye-open"></span>',
                        \yii\helpers\Url::to(['orders/open', 'id' => $data->id]),
                        ['title' => 'Открыть заказ']
                    );

                if ($data->status !== Orders::STATUS_PAYED) {
                    $adminContent .= '&nbsp;&nbsp;' . Html::a(
                            '<span class="glyphicon glyphicon-ruble"></span>',
                            \yii\helpers\Url::to(['orders/pay-order', 'id' => $data->id]),
                            ['title' => 'Снять средства с балансов']
                        );
                }
            }

            if(!Yii::$app->user->isGuest && Yii::$app->user->identity->isAdmin()) {
                $content .= $adminContent;
            }
            return $content;
        }
    ],
];

$adminButton = '';
if(!Yii::$app->user->isGuest && Yii::$app->user->identity->isAdmin()) {
    $columns[] = [
        'class' => 'yii\grid\ActionColumn',
        'template' => '{delete}',
        'buttons' => [
            'delete' => function ($url,$model, $key) {
                return Html::a(
                    '<span class="glyphicon glyphicon-eye-close"></span>',
                    $url,
                    ['title' => 'Скрыть заказ']
                );
            },
        ],
    ];

    $adminButton = '<span class="glyphicon glyphicon-plus-sign"></span> ' .
        Html::a('Создать новый заказ', \yii\helpers\Url::to(['orders/add']));
}


echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => $columns,
    'pager' => [
        'hideOnSinglePage' => true,
    ],
    'layout'=>"{items}\n{pager}"
]);

echo $adminButton;

