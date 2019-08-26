<?php
/* @var $this yii\web\View */

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$this->title = 'Пополнение баланса';
//$this->params['breadcrumbs'][] = $this->title;
?>
<h1><?= Html::encode($this->title) ?></h1>
<?php
$form = ActiveForm::begin(['id' => 'form-deposite']); ?>
    <?= $form->field($model, 'user_id')->dropDownList($users)->label('Пользователь') ?>
    <?= $form->field($model, 'order_id')->dropDownList($orders)->label('ID заказа') ?>
    <?= $form->field($model, 'sum')->textInput()->label('Сумма') ?>
    <?= $form->field($model, 'comment')->textInput(['value' => 'Пополнение баланса за заказ №'.array_pop($orders)])->label('Сумма') ?>

    <div class="form-group">
        <?= Html::submitButton('Отправить', ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
    </div>
<?php ActiveForm::end(); ?>

<h3>Должники:</h3>
<?php
foreach ($balanceList as $userId => $balance) {
    echo '<div>' .$users[$userId].": ".$balance. '</div>';
}

$this->registerJs('jQuery("#userbalancelog-order_id").on("change", function() {jQuery("userbalancelog-comment").val("Пополнение баланса за заказ №" + this.value)});');
