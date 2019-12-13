<?php
/**
 * Created by PhpStorm.
 * User: a.yanover
 * Date: 11.12.2019
 * Time: 11:07
 */

use app\widgets\Alert;

?>
<h1>Сообщить про доставку</h1>
<?= Alert::widget() ?>
<?php
//var_dump($res);
//var_dump(\app\models\Notification::getTokens());
$form = \yii\bootstrap\ActiveForm::begin();
echo $form->field($notificationModel, 'title')->textInput(['value' => 'Доставка заказа']);
echo $form->field($notificationModel, 'body')->textInput(['value' => 'Заказ будет доставлен с 1:00 до 1:00']);
echo $form->field($notificationModel, 'click_action')->textInput(['value' => 'https://' . $_SERVER['HTTP_HOST'].
    \yii\helpers\Url::to(['orders/view', 'id' => 1])]);
?>
<button type="submit" class="btn btn-primary">Отправить сообщение</button>
<?php \yii\bootstrap\ActiveForm::end();
