<?php
/* @var $this yii\web\View */

use yii\helpers\Html;

$this->title = 'Каталог товаров';
?>
<h1><?= Html::encode($this->title) ?></h1>
<div class="row">
    <?foreach ($categories as $category):?>
    <div class="col-xs-6 col-md-3 text-center" style=" border: #d5d5d5 1px dashed; padding: 10px 5px;">
        <?
            echo Html::a(
                    '<div style="height:80px; background: url(\''.$category->image_src.'\') no-repeat; background-position: center center;">
            </div><br>'.$category->name,
                    $category->url
            );
        ?>
    </div>
    <?endforeach;?>
</div>
