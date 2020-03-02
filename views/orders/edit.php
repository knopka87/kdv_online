<?php
/* @var $this yii\web\View
 * @var $order \app\models\Orders
 * @var \yii\data\ActiveDataProvider $positionProvider
 */

use app\models\OrderPositions;
use app\models\OrdersUsers;
use app\widgets\Alert;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

$isAdmin = !Yii::$app->user->isGuest && Yii::$app->user->identity->isAdmin();
?>
<h1>Заказ № <?=$order->id?> от <?=\Yii::$app->formatter->asDate($order->created_at, 'php:d.m.Y')?></h1>
    <br>
<?php $form = \yii\bootstrap\ActiveForm::begin([
    'layout' => 'inline'
])?>
<?=$form->field($ordersUsersModel, 'status')->hiddenInput(['value' => OrdersUsers::STATUS_DONE]);?>&nbsp;
    <button type="submit" class="btn btn-primary">Я всё!! На сегодня хватит!</button>
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
<?= $form->field($positionModel, 'amount')->textInput(['placeholder' => "Общее кол-во", 'value' => '1', 'type' => 'number'])->label(false)?>&nbsp;
    <button type="submit" class="btn btn-default">Добавить/изменить товар</button>
<?php \yii\bootstrap\ActiveForm::end();?>
    <br><br>
<?php
if (!$positionProvider) {
    echo 'Список товаров пуст';
}
else {
    echo Html::tag('h2', 'Мой заказ');
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
                'content' => function($data) use($positionModel) {
                    ob_start();
                    $form = \yii\bootstrap\ActiveForm::begin([
                        'layout' => 'inline',
                        'id' => 'PositionsUpdateForm'.$data['id'],
                    ]);
                    echo $form->field($positionModel, 'kdv_url')->hiddenInput(['value' => $data['kdv_url'], 'id' => 'kdv_url'.$data['id']]);

                    echo '<div class="input-group">
                                    <span class="input-group-btn">
                                        <button type="button" class="quantity-left-minus btn btn-danger btn-number"  data-type="minus" data-field="amount'.$data['id'].'">
                                          <span class="glyphicon glyphicon-minus"></span>
                                        </button>
                                    </span>
                                    <input type="text" id="amount'.$data['id'].'" name="OrderPositions[amount]" class="form-control input-number" value="'.$data['amount'].'" onchange="submit();">
                                    <span class="input-group-btn">
                                        <button type="button" class="quantity-right-plus btn btn-success btn-number" data-type="plus" >
                                            <span class="glyphicon glyphicon-plus"></span>
                                        </button>
                                    </span>
                              </div>';
                    \yii\bootstrap\ActiveForm::end();
                    return ob_get_clean();
                }
            ],
            [
                'attribute' => $isAdmin?'kdv_price':'price',
                'label' => 'Цена',

            ],
            [
                'attribute' => 'total',
                'label' => 'Сумма',
                'content' => function ($data) {
                    $isAdmin = !Yii::$app->user->isGuest && Yii::$app->user->identity->isAdmin();
                    if ($isAdmin) {
                        return $data['amount']*$data['kdv_price'];
                    }
                    return $data['amount']*$data['price'];
                },
                'footer' => "<b>" . OrderPositions::getTotalPrice($positionProvider->models) . "</b>",
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
<?php if (!empty($topUsedPosition)):?>
<br>
<h2>Ранее вы покупали</h2>
<br>
<?php foreach ($topUsedPosition as $item):?>
        <div class="row">
            <?php $form = \yii\bootstrap\ActiveForm::begin([
                'layout' => 'inline',
                'id' => 'PositionsUpdateForm'.$item['id'],
            ])?>

            <?= $form->field($positionModel, 'kdv_url')->hiddenInput(['value' => $item['kdv_url'], 'id' => 'kdv_url'.$item['id']])?>
            <div class="col-md-7 col-sm-7 col-xs-7 col-mob">
                <a href="<?=$item['kdv_url']?>" target="_blank"><?=$item['caption']?></a>
            </div>
            <div class="col-md-5 col-sm-5 col-xs-5 col-mob">
                <div class="input-group">
                                    <span class="input-group-btn">
                                        <button type="button" class="quantity-left-minus btn btn-danger btn-number"  data-type="minus" data-field="amount<?=$item['id']?>">
                                          <span class="glyphicon glyphicon-minus"></span>
                                        </button>
                                    </span>
                    <input type="text" id="amount<?=$item['id']?>" name="OrderPositions[amount]" class="form-control input-number" value="<?=$item['multiple']?>">
                    <span class="input-group-btn">
                                        <button type="button" class="quantity-right-plus btn btn-success btn-number" data-type="plus" data-field="amount<?=$item['id']?>">
                                            <span class="glyphicon glyphicon-plus"></span>
                                        </button>
                                    </span>
                </div>
            <input type="hidden" name="type" value="update_positions"/>
            <button type="submit" class="btn btn-default">Добавить/изменить товар</button>
            </div>
            <?php \yii\bootstrap\ActiveForm::end();?>
        </div>
<?php endforeach;?>

<?php endif;?>

<br>
<h2>Итоговый заказ</h2>
<?= GridView::widget([
    'dataProvider' => $totalPositionProvider,
    'columns' => [
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
            'attribute' => $isAdmin?'kdv_price':'price',
            'label' => 'Цена',

        ],
        [
            'attribute' => 'total',
            'label' => 'Сумма',
            'content'=>function($data) {
                $isAdmin = !Yii::$app->user->isGuest && Yii::$app->user->identity->isAdmin();
                if ($isAdmin) {
                    return round($data['amount']*$data['kdv_price'], 2);
                }
                return round($data['amount']*$data['price'], 2);
            },
            'footer' => '<b>' .OrderPositions::getTotalPrice($totalPositionProvider->models). '</b>',
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
        if($model['multiple'] !== 1 && ($model['amount'] % $model['multiple']) !== 0) {
            return ['style' => 'background-color:#ee9999;'];
        }
    },
]);
?>

<?php
$script = '
$("#PositionsUpdateForm").on("beforeSubmit", function () {
  $(this).find("button").prop("disabled", true);
  $("<span>Идёт поиск товара.. Ожидайте!</span>").appendTo(this);
  return true;  
})';
$this->registerJs($script);
Pjax::end();
