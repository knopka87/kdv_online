<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\widgets\Alert;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => Yii::$app->name,
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top navbar-collapse',
        ],
    ]);

    if (!Yii::$app->user->isGuest) {
        $leftItems[] = '<li>'.\app\models\UserBalance::getBalanceHtml(). '</li>';
        $leftItems[] = [
            'label' => 'Donate',
            'url' => ['balance/donate'],
            'linkOptions' => ['style' => 'background-color: #fcf8e3; color: #777;']
        ];
        echo Nav::widget([
            'options' => ['class' => 'navbar-nav navbar-left'],
            'items' => $leftItems
        ]);
    }


    $items = [
            ['label' => 'Авторизоваться', 'url' => ['/site/login']],
            ['label' => 'Зарегистрироваться', 'url' => ['/site/signup']]
    ];
    if (!Yii::$app->user->isGuest) {
        $items = [

            ['label' => 'Каталог', 'url' => ['/catalog/index']],
            ['label' => 'Список заказов', 'url' => ['/orders/list']],
            ['label' => 'Финансы', 'url' => ['/balance/index']],
            ['label' => 'Доска почёта', 'url' => ['/statistics/index']],
            '<li>'
                . Html::beginForm(['/site/logout'], 'post')
                . Html::submitButton(
                    'Выйти (<span id="username" data-id="'.Yii::$app->user->id.'">' . Yii::$app->user->identity->username . '</span>)',
                    ['class' => 'btn btn-link logout']
                )
                . Html::endForm()
                . '</li>',
            '<li><a href="#" id="subscribe" class="glyphicon glyphicon-envelope" style="color: white;" title="Подписаться на уведомления"></a></li>'
        ];
    }
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => $items,
    ]);
    NavBar::end();
    ?>

    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>

</div>

<?php
    $this->endBody();
    $this->registerJsFile('//www.gstatic.com/firebasejs/3.6.8/firebase.js');
    $this->registerJsFile('/firebase_subscribe.js');
?>
<script type="text/javascript">
    var reformalOptions = {
        project_id: 983757,
        project_host: "tradesoft-kdv.reformal.ru",
        tab_orientation: "bottom-right",
        tab_indent: "10px",
        tab_bg_color: "#F05A00",
        tab_border_color: "#FFFFFF",
        tab_image_url: "http://tab.reformal.ru/T9GC0LfRi9Cy0Ysg0Lgg0L%252FRgNC10LTQu9C%252B0LbQtdC90LjRjw==/FFFFFF/07330bc5004dd1d3a4e80c777f77d3dc/bottom-right/0/tab.png",
        tab_border_width: 0
    };

    (function() {
        var script = document.createElement('script');
        script.type = 'text/javascript'; script.async = true;
        script.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'media.reformal.ru/widgets/v3/reformal.js';
        document.getElementsByTagName('head')[0].appendChild(script);
    })();
</script><noscript><a href="http://reformal.ru"><img src="http://media.reformal.ru/reformal.png" /></a><a href="http://tradesoft-kdv.reformal.ru">Oтзывы и предложения для КДВ</a></noscript>
</body>
</html>
<?php $this->endPage();
?>
