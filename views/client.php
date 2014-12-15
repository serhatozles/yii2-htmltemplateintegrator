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
	<hr />
    	<div class="row">
    	    <div class="col-md-6 text-center">
    		<div class="panel panel-default">
    		    <div class="panel-body">
			    <?php echo Html::label('Header Tag Selector:'); ?>
			    <?php echo Html::input('text', 'headerselector', 'header.header', ['class' => 'form-control']); ?><hr />
			    <?php echo Html::label('Content Tag Selector:'); ?>
			    <?php echo Html::input('text', 'contentselector', 'section.content', ['class' => 'form-control']); ?><hr />
			    <?php echo Html::label('Footer Tag Selector:'); ?>
			    <?php echo Html::input('text', 'footerselector', 'footer.footer', ['class' => 'form-control']); ?>
    		    </div>
    		</div>
    	    </div>
    	    <div class="col-md-6 text-center">
    		<div class="panel panel-default">
    		    <div class="panel-body">
			    <?php echo Html::label('Template:'); ?>
			    <?php echo Html::dropDownList('folder', null, $themeList, ['class' => 'form-control']); ?>
    		    </div>
    		    <div class="panel-footer"><?php echo Html::submitButton('Next', ['class' => 'btn btn-success']); ?></div>
    		</div>
    	    </div>
    	</div>
	    <?php echo Html::endForm(); ?>
	    <?php
	} else {
	    echo '<div class="alert alert-danger">Couldn\'t find a template.</div>';
	}
	?>
    </div>
</div>
<?php $this->endContent(); ?>
