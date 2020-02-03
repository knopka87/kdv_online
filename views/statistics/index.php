<?php
/**
 * @var $this yii\web\View
 * @var array $donateList
 * @var array $users
 *
**/

use app\models\OrderPositions;
use yii\helpers\Html;

$this->registerCssFile('@web/css/style-75.css');
?>
<h1 class="text-center">Доска почёта</h1>
<br>
<h2>Пользователи</h2>
<div class="row ui-75">
    <div class="col-md-3 col-sm-6 col-xs-6 col-mob">
        <div class="ui-item">
            <div class="bg-red text-center">
                <h2>Донатчики</h2>
                <small>Больше всех внесли пожертвований</small>
            </div>
            <ul class="clearfix">
                <?php foreach ($donateList as $donate) :?>
                <li>
                    <?=$users[$donate['user_id']]?>
                    <span><?=OrderPositions::getDischangeHTML($donate['sum'], 2)?> р.</span>
                </li>
                <?php endforeach;?>
            </ul>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-6 col-mob">
        <div class="ui-item">
            <div class="bg-yellow text-center">
                <h2>Коллекционеры</h2>
                <small>Больше всех заказали единиц товара</small>
            </div>
            <ul class="clearfix">
                <?php foreach ($countPositionsListByUser as $countPositions):?>
                    <li>
                        <?=$users[$countPositions['user_id']]?>
                        <span><?=$countPositions['count_pos']?> шт.</span>
                    </li>
                <?php endforeach;?>
            </ul>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-6 col-mob">
        <div class="ui-item">
            <div class="bg-brown text-center">
                <h2>Тяжеловесы</h2>
                <small>Больше всех заказали по весу</small>
            </div>
            <ul class="clearfix">
                <?php foreach ($weightListByUser as $key => $weight):?>
                    <li>
                        <?=$users[$weight['user_id']]?>
                        <span><?=OrderPositions::getDischangeHTML($weight['count_pos']/1000, 3)?> кг.</span>
                    </li>
                <?php endforeach;?>
            </ul>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-6 col-mob">
        <div class="ui-item">
            <div class="bg-green text-center">
                <h2>Транжиры</h2>
                <small>Потратил больше всех на заказы</small>
            </div>
            <ul class="clearfix">
                <?php foreach ($writeOffList as $key => $writeOff):?>
                    <li>
                        <?=$users[$writeOff['user_id']]?>
                        <span><?=OrderPositions::getDischangeHTML($writeOff['sum'], 2)?> р.</span>
                    </li>
                <?php endforeach;?>
            </ul>
        </div>
    </div>
</div>
<h2>Заказы</h2>
<div class="row ui-75">
    <div class="col-md-3 col-sm-6 col-xs-6 col-mob">
        <div class="ui-item">
            <div class="bg-red text-center">
                <h2>Собиратели душ</h2>
                <small>Больше всех покупателей в заказе</small>
            </div>
            <ul class="clearfix">
                <?php foreach ($countUsersListByOrder as $countUser):?>
                    <li>
                        <?=Html::a(
                        'Заказ №'.$countUser['order_id'],
                        \yii\helpers\Url::to(['orders/view', 'id' => $countUser['order_id']])
                        )?>
                        <span><?=$countUser['sum']?> чел.</span>
                    </li>
                <?php endforeach;?>
            </ul>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-6 col-mob">
        <div class="ui-item">
            <div class="bg-yellow text-center">
                <h2>Коллекционеры</h2>
                <small>Больше всех единиц товара в заказе</small>
            </div>
            <ul class="clearfix">
                <?php foreach ($countPositionsListByOrder as $key => $countPositions):?>
                    <li>
                        <?=Html::a(
                        'Заказ №'.$countPositions['order_id'],
                        \yii\helpers\Url::to(['orders/view', 'id' => $countPositions['order_id']])
                        )?>
                        <span><?=$countPositions['sum']?> шт.</span>
                    </li>
                <?php endforeach;?>
            </ul>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-6 col-mob">
        <div class="ui-item">
            <div class="bg-brown text-center">
                <h2>Тяжеловесы</h2>
                <small>Самые тяжёлые заказы</small>
            </div>
            <ul class="clearfix">
                <?php foreach ($weightListByOrder as $key => $weight):?>
                    <li>
                        <?=Html::a(
                        'Заказ №'.$weight['order_id'],
                        \yii\helpers\Url::to(['orders/view', 'id' => $weight['order_id']])
                        )?>
                        <span><?=OrderPositions::getDischangeHTML($weight['weight']/1000, 3)?> кг.</span>
                    </li>
                <?php endforeach;?>
            </ul>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-6 col-mob">
        <div class="ui-item">
            <div class="bg-green text-center">
                <h2>Транжиры</h2>
                <small>Самые дорогие заказы</small>
            </div>
            <ul class="clearfix">
                <?php foreach ($totalPriceListByOrder as $totalPrice):?>
                    <li>
                        <?=Html::a(
                        'Заказ №'.$totalPrice['order_id'],
                        \yii\helpers\Url::to(['orders/view', 'id' => $totalPrice['order_id']])
                        )?>
                        <span><?=OrderPositions::getDischangeHTML($totalPrice['sum'], 2)?> р.</span>
                    </li>
                <?php endforeach;?>
            </ul>
        </div>
    </div>
</div>
<h2>Товары</h2>
<div class="row ui-75">
    <div class="col-md-6 col-sm-6 col-xs-6 col-mob">
        <div class="ui-item">
            <div class="bg-green text-center">
                <h2>Любимчик</h2>
                <small>Больше всех раз заказывали</small>
            </div>
            <ul class="clearfix">
                <?php foreach ($popularPositions as $position):?>
                    <li>
                        <div class="row">
                            <div class="col-md-9">
                                <?=Html::a(
                                    $position['caption'],
                                    $position['kdv_url']
                                )?>
                            </div>
                            <div class="col-md-3">
                                <?=$position['count']?> раз.
                            </div>
                        </div>
                    </li>
                <?php endforeach;?>
            </ul>
        </div>
    </div>
    <div class="col-md-6 col-sm-6 col-xs-6 col-mob">
        <div class="ui-item">
            <div class="bg-green text-center">
                <h2>Любимчик 2</h2>
                <small>Больше всего штук было заказано</small>
            </div>
            <ul class="clearfix">
                <?php foreach ($topAmountPositions as $position):?>
                    <li>
                        <div class="row">
                            <div class="col-md-10">
                                <?=Html::a(
                                    $position['caption'],
                                    $position['kdv_url']
                                )?>
                            </div>
                            <div class="col-md-2">
                                <?=$position['count']?> шт.
                            </div>
                        </div>
                    </li>
                <?php endforeach;?>
            </ul>
        </div>
    </div>

</div>
<?php
