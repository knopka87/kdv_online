<?php
/* @var $this yii\web\View */

use app\models\OrderPositions;

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
                <?foreach ($donateList as $key => $donate):?>
                <li>
                    <?=$users[$donate['user_id']]?>
                    <span><?=OrderPositions::getDischangeHTML($donate['sum'], 2)?> р.</span>
                </li>
                <?endforeach;?>
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
                <?foreach ($countPositionsListByUser as $key => $countPositions):?>
                    <li>
                        <?=$users[$countPositions['user_id']]?>
                        <span><?=$countPositions['count_pos']?> шт.</span>
                    </li>
                <?endforeach;?>
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
                <?foreach ($weightListByUser as $key => $weight):?>
                    <li>
                        <?=$users[$weight['user_id']]?>
                        <span><?=OrderPositions::getDischangeHTML($weight['count_pos']/1000, 3)?> кг.</span>
                    </li>
                <?endforeach;?>
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
                <?foreach ($writeOffList as $key => $writeOff):?>
                    <li>
                        <?=$users[$writeOff['user_id']]?>
                        <span><?=OrderPositions::getDischangeHTML($writeOff['sum'], 2)?> р.</span>
                    </li>
                <?endforeach;?>
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
                <?foreach ($countUsersListByOrder as $countUser):?>
                    <li>
                        Заказ №<?=$countUser['order_id']?>
                        <span><?=$countUser['sum']?> чел.</span>
                    </li>
                <?endforeach;?>
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
                <?foreach ($countPositionsListByOrder as $key => $countPositions):?>
                    <li>
                        Заказ №<?=$countPositions['order_id']?>
                        <span><?=$countPositions['sum']?> шт.</span>
                    </li>
                <?endforeach;?>
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
                <?foreach ($weightListByOrder as $key => $weight):?>
                    <li>
                        Заказ №<?=$weight['order_id']?>
                        <span><?=OrderPositions::getDischangeHTML($weight['weight']/1000, 3)?> кг.</span>
                    </li>
                <?endforeach;?>
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
                <?foreach ($totalPriceListByOrder as $totalPrice):?>
                    <li>
                        Заказ №<?=$totalPrice['order_id']?>
                        <span><?=OrderPositions::getDischangeHTML($totalPrice['sum'], 2)?> р.</span>
                    </li>
                <?endforeach;?>
            </ul>
        </div>
    </div>
</div>
<h2>Товары</h2>
<div class="row ui-75">
    <div class="col-md-4 col-sm-6 col-xs-6 col-mob">
        <div class="ui-item">
            <div class="bg-green text-center">
                <h2>Любимчик</h2>
                <small>Больше всех раз заказывали</small>
            </div>
            <ul class="clearfix">
                <?foreach ($popularPositions as $position):?>
                    <li>
                        <div class="row">
                            <div class="col-md-9">
                                <?=$position['caption']?>
                            </div>
                            <div class="col-md-3">
                                <?=$position['count']?> раз.
                            </div>
                        </div>
                    </li>
                <?endforeach;?>
            </ul>
        </div>
    </div>
    <div class="col-md-4 col-sm-6 col-xs-6 col-mob">
        <div class="ui-item">
            <div class="bg-green text-center">
                <h2>Любимчик 2</h2>
                <small>Больше всего штук было заказано</small>
            </div>
            <ul class="clearfix">
                <?foreach ($topAmountPositions as $position):?>
                    <li>
                        <div class="row">
                            <div class="col-md-9">
                                <?=$position['caption']?>
                            </div>
                            <div class="col-md-3">
                                <?=$position['count']?> шт.
                            </div>
                        </div>
                    </li>
                <?endforeach;?>
            </ul>
        </div>
    </div>

</div>