<?php
use yii\helpers\Html;
$this->beginContent(__DIR__ . '/layouts/main.php'); print_r($themeList);?>
<?php echo Html::beginForm(); ?>
<?php echo Html::dropDownList('folder', null, $themeList); ?>
<?php echo Html::submitButton('Submit', ['class' => 'btn btn-default']); ?>
<?php echo Html::endForm(); ?>
<?php $this->endContent(); ?>
