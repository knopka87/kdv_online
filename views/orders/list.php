<?php
/* @var $this yii\web\View
 * @var $dataProvider \yii\data\ActiveDataProvider
 */

use app\models\Users;
use yii\grid\GridView;
use yii\helpers\Html;


$this->title = 'Список заказов';
//$this->params['breadcrumbs'][] = $this->title;
?>
    <h1><?= Html::encode($this->title) ?></h1>

<?php

$columns = [
    [
        'attribute'=>'created_at',
        'label'=>'Заказы',
        'format'=>'date', // Доступные модификаторы - date:datetime:time
        'content'=>function($data) {
            $content = Html::a(
                    'Заказ от ' . \Yii::$app->formatter->asDate($data->created_at, 'php:d.m.Y'),
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

            if ($data->status == \app\models\Orders::STATUS_ACTIVE) {
                $content = 'В процессе..';
                $adminContent = '&nbsp;&nbsp;'.Html::a(
                        '<span class="glyphicon glyphicon-saved"></span>',
                        \yii\helpers\Url::to(['orders/close', 'id' => $data->id]),
                        ['title' => 'Завершить заказ']
                    );
            }
            else {
                $content = 'Завершён';
                $adminContent = '&nbsp;&nbsp;'.Html::a(
                        '<span class="glyphicon glyphicon-eye-open"></span>',
                        \yii\helpers\Url::to(['orders/open', 'id' => $data->id]),
                        ['title' => 'Открыть заказ']
                    );
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

