<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use frontend\assets\AppAsset;
use frontend\widgets\Alert;

/* @var $this \yii\web\View */
/* @var $content string */

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
	<meta charset="<?= Yii::$app->charset ?>"/>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?= Html::csrfMetaTags() ?>
    <title>HTML Template Integrator</title>
    <?php $this->head() ?>
</head>
<body>
    <?php $this->beginBody() ?>
<div class="wrap">
    <?php
    NavBar::begin([
	'brandLabel' => 'HTML Template Integrator',
	'brandUrl' => Url::to(['integrator/define']),
	'options' => [
	    'class' => 'navbar-inverse',
	],
    ]);


    $menuItems = [
	['label' => 'Project Page', 'url' => 'https://github.com/serhatozles/yii2-htmltemplateintegrator', 'linkOptions' => ['target' => '_blank']],
    ];

    echo Nav::widget([
	'options' => ['class' => 'navbar-nav navbar-right'],
	'items' => $menuItems,
    ]);
    NavBar::end();
    ?>

    <div class="container" style="padding-top:0px;">
	<?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">Author: <?php echo Html::a('Serhat Özleş','https://github.com/serhatozles',['target' => '_blank']); ?></p>
        <p class="pull-right"><?= Yii::powered() ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
