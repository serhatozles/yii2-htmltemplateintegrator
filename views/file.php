<?php

use kartik\helpers\Html;

$js = '
var ControllerTemplate = $(".ControllerTemplate").html();

var counter = 0;
var ControllerName = "' . $MainControllerName . '";
$(document).on("click",".addController",function(){
    counter++;
    var ControllerTemplateNew = ControllerTemplate.replace("ControllerNameChange[]","controllerName[" + counter + "]");
    ControllerTemplateNew = ControllerTemplateNew.replace("ControllerActionChange[]","controllerAction[" + counter + "][]");
    ControllerTemplateNew = ControllerTemplateNew.replace(ControllerName,ControllerName + counter);
    $(".Controllers").append(ControllerTemplateNew);
});
$(document).on("click",".removeController",function(){
    if($(".Controllers .ControllerList").length > 1){
	$(this).closest(".ControllerList").remove();
    }else{
	alert("You can\'t this because you have a controller!");
    }
});
    ';
$this->registerJs($js, \yii\web\View::POS_READY);

$this->beginContent(__DIR__ . '/layouts/main.php');
?>
<div class="row">
    <div class="col-md-12 text-center">
	<?php
	echo '<h5>Firstly, you have to put your template folder into @app/template. ' . Html::bsLabel('Required', Html::TYPE_DANGER) . '</h5>';

	if (count($fileList) > 0) {
	    ?>
    	<div class="ControllerTemplate" style="display:none;">
    	    <div class="col-md-4 ControllerList">
    		<div class="panel panel-default">
    		    <div class="panel-body">
			    <?php echo Html::label('Controller Name:'); ?>
			    <?php echo Html::input('text', 'ControllerNameChange[]', $MainControllerName, ['class' => 'form-control']); ?><br />
			    <?php echo Html::label('Actions:'); ?>
			    <?php echo Html::dropDownList('ControllerActionChange[]', $ActionList, $ActionList, ['class' => 'form-control', 'multiple' => true, 'size' => 6]); ?><br />
			    <?php echo Html::button('Remove', ['class' => 'btn btn-danger removeController']); ?>
    		    </div>
    		</div>
    	    </div>
    	</div>
	    <?php echo Html::beginForm(); ?>
    	<hr/>
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
    	</div><hr/>
	    <?php echo Html::label('Which file will use for layout?:'); ?>
	    <?php echo Html::dropDownList('file', null, $fileList, ['class' => 'form-control']); ?>
    	<hr/>
	    <?php echo Html::label('Controllers:'); ?>
    	<div class="row Controllers">
    	    <div class="col-md-4 ControllerList">
    		<div class="panel panel-default">
    		    <div class="panel-body">
			    <?php echo Html::label('Controller Name:'); ?>
			    <?php echo Html::input('text', 'controllerName[0]', $MainControllerName, ['class' => 'form-control']); ?><br />
			    <?php echo Html::label('Actions:'); ?>
			    <?php echo Html::dropDownList('controllerAction[0][]', $ActionList, $ActionList, ['class' => 'form-control', 'multiple' => true, 'size' => 6]); ?><br />
			    <?php echo Html::button('Remove', ['class' => 'btn btn-danger removeController']); ?>
    		    </div>
    		</div>
    	    </div>
    	</div>
	    <?php echo Html::button('Add Controller', ['class' => 'btn btn-success addController']); ?>
    	<hr/>
	    <?php echo Html::input('hidden', 'folder', $folder); ?>
	    <?php echo Html::input('hidden', 'step', '2'); ?>
	    <?php echo Html::submitButton('Generate', ['class' => 'btn btn-primary']); ?>
	    <?php echo Html::endForm(); ?>
	    <?php
	} else {
	    echo '<div class="alert alert-danger">Couldn\'t find a file.</div>';
	}
	?>
    </div>
</div>
<?php $this->endContent(); ?>
