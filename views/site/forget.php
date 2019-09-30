<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $user app\models\LoginForm */

use app\widgets\Alert;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Восстановление пароля';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-forget">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php
    $error = \Yii::$app->session->getFlash('error');
        if (!empty($error)){
            echo Alert::widget();
        } else {

            $form = ActiveForm::begin([
                'id' => 'forget-form'
            ]);

            echo $form->field($model, 'password')->passwordInput()->label('Пароль');

            echo Html::submitButton('Go!', ['class' => 'btn btn-primary', 'name' => 'login-button']);


         ActiveForm::end();
    }?>
</div>