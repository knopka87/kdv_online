<?php
/* @var $this yii\web\View
 * @var $order \app\models\Orders
 * @var \yii\data\ActiveDataProvider $positionProvider
 */

use app\models\OrderPositions;
use app\widgets\Alert;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

?>
<h1>Заказ № <?=$order->id?> от <?=\Yii::$app->formatter->asDate($order->created_at, 'php:d.m.Y')?></h1>
    <br>
<?php
    Pjax::begin([
    'formSelector' => '#PositionsUpdateForm'
    ]);
?>
<?php $form = \yii\bootstrap\ActiveForm::begin([
    'layout' => 'inline',
    'id' => 'PositionsUpdateForm',
])?>

<?= Alert::widget() ?>

<?= $form->field($positionModel, 'kdv_url')->textInput(['placeholder' => "Kdv url"])->label(false);?>&nbsp;
<?= $form->field($positionModel, 'amount')->textInput(['placeholder' => "Общее кол-во"])->label(false)?>&nbsp;
    <button type="submit" class="btn btn-default">Добавить/изменить товар</button>
<?php \yii\bootstrap\ActiveForm::end();?>
<br><br>
<?php
if (!$positionProvider) {
    echo 'Список товаров пуст';
}
else {
    echo GridView::widget([
        'dataProvider' => $positionProvider,
        'columns' => [
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

            ],
            [
                'attribute' => 'total',
                'label' => 'Сумма',
                'content' => function ($data) {
                    return $data->amount * $data->price;
                },
                'footer' => "<b>" . OrderPositions::getTotalBalance($positionProvider->models) . "</b>",
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{delete}',
                'buttons' => [
                    'delete' => function ($url, $model, $key) {
                        return Html::a(
                            '<span class="glyphicon glyphicon-trash"></span>',
                            \yii\helpers\Url::to(['positions/delete', 'id' => $key, 'orderId' => $model->order_id]),
                            [
                                'data-pjax' => '#model-grid',
                                'title' => Yii::t('app', 'Delete')
                            ]
                        );
                    },
                ]
            ],
        ],
        'showFooter' => true,
        'summary' => false,
    ]);
}?>
<?php
Pjax::end();

$script = '
$("#PositionsUpdateForm").on("beforeSubmit", function () {
  $(this).find("button").prop("disabled", true);
  $("<span>Идёт поиск товара.. Ожидайте!</span>").appendTo(this);
  return true;  
})';
$this->registerJs($script);