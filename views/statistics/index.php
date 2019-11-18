<?php
/* @var $this yii\web\View */

use app\models\OrderPositions;

$this->registerCssFile('@web/css/style-75.css');
?>
<h1>Доска почёта</h1>

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
                <?foreach ($countPositionsList as $key => $countPositions):?>
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
                <?foreach ($weightList as $key => $weight):?>
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
                <small>Потратил больше все на заказы</small>
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