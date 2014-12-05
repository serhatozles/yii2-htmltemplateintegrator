<?php

use kartik\helpers\Html;

$this->beginContent(__DIR__ . '/layouts/main.php');
?>
<div class="row">
    <div class="col-md-12 text-center">
	<?php
	echo '<h5>Firstly, you have to put your template folder into @app/template. ' . Html::bsLabel('Required', Html::TYPE_DANGER) . '</h5>';

	if (count($themeList) > 0) {
	    ?>
	    <?php echo Html::beginForm(); ?>
	    <?php echo Html::input('hidden', 'step', '1'); ?>
	    <?php echo Html::label('Template:'); ?>
	    <?php echo Html::dropDownList('folder', null, $themeList, ['class' => 'form-control']); ?><br /><br />
	    <?php echo Html::submitButton('Submit', ['class' => 'btn btn-default']); ?>
	    <?php echo Html::endForm(); ?>
	    <?php
	} else {
	    echo '<div class="alert alert-danger">Couldn\'t find a template.</div>';
	}
	?>
    </div>
</div>
<?php $this->endContent(); ?>
