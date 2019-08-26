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
<?php
echo GridView::widget([
    'dataProvider' => $balanceProvider,
    'columns' => [
        [
            'attribute' => 'comment',
            'label' => 'Описание',
            'footer' => '<b>Итого:</b>'
        ],
        [
            'attribute' => 'sum',
            'label' => 'Сумма, р.',
            'content'=> function($data) {
                $class = ($data->sum>=0)?'success':'danger';
                return '<span class="label label-'.$class.'">'.$data->sum.'</span>';
            },
            'footer' => "<b>" . UserBalanceLog::getTotalSum($balanceProvider->models) . "</b>",
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
    'showFooter' => true
]);

if (Yii::$app->user->identity->isAdmin()) {
    echo '<span class="glyphicon glyphicon-plus-sign"></span> ' .
    Html::a('Внести оплату', \yii\helpers\Url::to(['balance/deposite']));
}


