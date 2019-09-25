<?php
/* @var $this yii\web\View */

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Пополнение баланса');
//$this->params['breadcrumbs'][] = $this->title;
$commentText = Yii::t('app', 'Пополнение баланса за заказ №');
$donateText = Yii::t('app', 'Пополнение баланса');
?>
<h1><?= Html::encode($this->title) ?></h1>
<?php

$form = ActiveForm::begin(['id' => 'form-deposite']); ?>
    <?= $form->field($model, 'user_id')->dropDownList($users)->label(Yii::t('app', 'Пользователь')) ?>
    <?= $form->field($model, 'order_id')->widget(\yii\jui\AutoComplete::classname(), [
    'clientOptions' => [
        'source' => array_merge($orders , [['value' => '0', 'label' => 'Donate']]),
    ],
    'options'=>[
        'class'=>'form-control',
        'value' => current($orders)['value']
    ],
])->label(Yii::t('app', 'ID заказа')) ?>
    <?= $form->field($model, 'sum')->textInput()->label(Yii::t('app', 'Сумма')) ?>
    <?= $form->field($model, 'comment')->textInput(['value' => $commentText.current($orders)['value']])->label(Yii::t('app', 'Сумма')) ?>

    <div class="form-group">
        <?= Html::submitButton(
                Yii::t('app', 'Отправить'),
                ['class' => 'btn btn-primary', 'name' => 'signup-button']
        )?>
    </div>
<?php ActiveForm::end(); ?>

<h3><?=Yii::t('app', 'Должники')?>:</h3>
<?php
foreach ($balanceList as $userId => $balance) {
    echo '<div>' .$users[$userId]. ': ' .$balance. '</div>';
}

$this->registerJs('jQuery("#userbalancelog-order_id").on("change", function() {
    if (this.value == 0) {
        jQuery("#userbalancelog-comment").val("' . $donateText . '");
    } else {
        jQuery("#userbalancelog-comment").val("' . $commentText . '" + this.value);
    }
});');
