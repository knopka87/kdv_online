<?php
/* @var $this yii\web\View */

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Markdown;

$this->title = Yii::t('app', 'Donate');
?>
<h1><?php echo Html::encode($this->title) ?></h1>

<p>Если вы хотите поддержать данный проект, чтобы он и дальше становится лучше и облегчал совместный заказ на КДВ, то <s>подайте сколько не жалко</s> можете сделать добровольный взнос на развитие проекта (перевести денежки со своего внутреннего баланса на баланс Сашки). А он за это будет вам очень благодарен! И будет ещё усерднее трудиться на общее благо!</p>
<?php
$form = ActiveForm::begin(['id' => 'form-deposite']); ?>
<?= $form->field($model, 'sum')->textInput()->label(Yii::t('app', 'Сумма, р.')) ?>

<div class="form-group">
    <?= Html::submitButton(
        Yii::t('app', 'Donate!'),
        ['class' => 'btn btn-primary', 'name' => 'signup-button']
    )?>
</div>
<?php ActiveForm::end(); ?>
    <div class="container">
        <div class="row">
            <div class="col-md-6 col-sm-6 col-xs-12 text-success">
                <h2>Список сделанных изменений на сайте:</h2>
                <?=Markdown::process(file_get_contents(__DIR__.'../../../changelog.md'), 'gfm');?>
            </div>
            <div class="col-md-6 col-sm-6 col-xs-12 text-info">
                <h2>Планы на будущее:</h2>
                <?=Markdown::process(file_get_contents(__DIR__.'../../../issue.md'), 'gfm');?>
            </div>
        </div>
    </div>
