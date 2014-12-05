<?php

use kartik\helpers\Html;

$this->beginContent(__DIR__ . '/layouts/main.php');
?>
<div class="row">
    <div class="col-md-12 text-center">
	<?php
	echo '<h5>Firstly, you have to put your template folder into @app/template. ' . Html::bsLabel('Required', Html::TYPE_DANGER) . '</h5>';

	if (count($fileList) > 0) {
	    ?>
	    <?php echo Html::beginForm(); ?>
    	<div class="row">
    	    <div class="col-md-4 text-center">
		    <?php echo Html::label('Header Tag Selector:'); ?>
		    <?php echo Html::input('text', 'headerselector', 'header.header', ['class' => 'form-control']); ?>
    	    </div>
    	    <div class="col-md-4 text-center">
		    <?php echo Html::label('Content Tag Selector:'); ?>
		    <?php echo Html::input('text', 'contentselector', 'section.content', ['class' => 'form-control']); ?>
    	    </div>
    	    <div class="col-md-4 text-center">
		    <?php echo Html::label('Footer Tag Selector:'); ?>
		    <?php echo Html::input('text', 'footerselector', 'footer.footer', ['class' => 'form-control']); ?>
    	    </div>
    	</div>
	    <?php echo Html::input('hidden', 'folder', $folder); ?>
	    <?php echo Html::input('hidden', 'step', '2'); ?>
	    <?php echo Html::label('Which file will use for layout?:'); ?>
	    <?php echo Html::dropDownList('file', null, $fileList, ['class' => 'form-control']); ?><br /><br />
	    <?php echo Html::submitButton('Submit', ['class' => 'btn btn-default']); ?>
	    <?php echo Html::endForm(); ?>
	    <?php
	} else {
	    echo '<div class="alert alert-danger">Couldn\'t find a file.</div>';
	}
	?>
    </div>
</div>
<?php $this->endContent(); ?>
