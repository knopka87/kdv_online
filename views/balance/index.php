<?php
/* @var $this yii\web\View */

use app\models\UserBalanceLog;
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Статистика изменения баланса';
//$this->params['breadcrumbs'][] = $this->title;
?>
<h1><?= Html::encode($this->title) ?></h1>
<br>
<span class="glyphicon glyphicon-plus-sign"></span>
<?=Html::a('Пополнить баланс', \app\models\UserBalance::getTinkoffLink(), ['target' => '_blank'])?>
<br><br>
<?php
echo GridView::widget([
    'dataProvider' => $balanceProvider,
    'columns' => [
        [
            'attribute' => 'comment',
            'label' => 'Описание'
        ],
        [
            'attribute' => 'sum',
            'label' => 'Сумма, р.',
            'content'=> function($data) {
                $class = ($data->sum>=0)?'success':'danger';
                return '<span class="label label-'.$class.'">'.$data->sum.'</span>';
            },
        ],
        [
            'attribute' => 'created_at',
            'label' => 'Дата',
            'content'=> function($data) {
                return \Yii::$app->formatter->asDate($data->created_at, 'php:d.m.Y');
            }
        ]
    ],
    'pager' => [
        'hideOnSinglePage' => true,
    ],
    'layout'=>"{items}\n{pager}",
]);

if (Yii::$app->user->identity->isAdmin()) {
    echo '<span class="glyphicon glyphicon-plus-sign"></span> ' .
    Html::a('Внести оплату', \yii\helpers\Url::to(['balance/deposite']));
}


