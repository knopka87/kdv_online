<?php
/* @var $this yii\web\View */

use app\models\Tools;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;

$this->title = $catalogName;


$this->registerJsFile('@web/js/bootstrap-notify.min.js', ['depends' => [
    'yii\web\YiiAsset',
    'yii\web\JqueryAsset'
]]);
?>
<h1><?= Html::encode($this->title) ?></h1>
<br>
<?php
echo Breadcrumbs::widget([
    'links' => [
        [
            'label' => 'Каталог',
            'url' => ['catalog/index']
        ],
        [
            'label' => $this->title
        ]
    ],
]);
?>
<br>
<div class="container-fluid">
    <div class="row row-flex">
        <?foreach ($items as $item):?>
        <div class="col-md-3 col-sm-6 text-center" style=" border: #d5d5d5 1px dashed; padding: 20px 10px;">
            <div style="height:220px; background: url('<?=$item['img']?>') no-repeat; background-position: center center;">
            </div>
            <p><?=$item['name']?></p>
            <div class="row">
                <div class="col-xs-4 text-right">
                    <strong><?=Tools::priceFormat($item['price'])?>р.</strong>
                </div>
                <div class="col-xs-8 text-center">
                    <?if (!$order && $order->status == \app\models\Orders::STATUS_ACTIVE) :?>
                    <?=Html::input('number', 'amount', $item['multiple'], ['min' => 1, 'style' => ['width' => '50px', 'text-align' => 'right'], 'id' => 'amount-'.$item['id']])?> <?=$item['unit']?>
                    <?=Html::a(
                            'Добавить',
                            '#',
                            [
                                'class' => 'glyphicon glyphicon-shopping-cart',
                                'onclick' => 'return addBasket("'.$item['kdv_url'].'", '.$order->id.', "'.\yii\helpers\Url::to(['/orders/view/', 'id' => $order->id]).'", '.$item['id'].');'
                            ]
                    )?>
                    <?endif;?>
                </div>
            </div>
        </div>
        <?endforeach;?>
    </div>
</div>
<?if (!$order && $order->status == \app\models\Orders::STATUS_ACTIVE) :?>
<script>
    function addBasket(kdvUrl, orderId, link, id) {
        $.ajax({
            type: "POST",
            url: link,
            data: {
                type: "update_positions",
                ajax: true,
                OrderPositions: {
                    kdv_url: kdvUrl,
                    amount: $("#amount-" + id).val()
                }
            },
            success: function(result) {
                $.notify(result+'<br><a href="'+link+'">Перейти к заказу.</a>');
            },
            error: function(result) {
                console.log("server error");
            }
        });
        return false;
    }
</script>
<?endif;?>
