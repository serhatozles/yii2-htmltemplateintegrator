<?php

use kartik\helpers\Html;
use yii\jui\AutoComplete;

$ModelCode = ["find()","findOne()",'find()->where([])'];

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


$js = '
var ModelTemplate = $(".ModelListTemplate").html();

var counterModel = 0;
$(document).on("click",".addModel",function(){
    var modelNameAutoComplete = $("#modelNameAutoComplete").val();
    var modelGenerateAction = $("#modelGenerateAction").val();
    $("#modelNameAutoComplete").val("");
    if(modelNameAutoComplete != ""){
	var ModelTemplateNew = ModelTemplate.replace("{ACTIONNAME}",modelGenerateAction);
	ModelTemplateNew = ModelTemplateNew.replace("{ACTIONHIDDENNAME}",modelGenerateAction);
	ModelTemplateNew = ModelTemplateNew.replace("{MODELNAME}",modelNameAutoComplete);
	ModelTemplateNew = ModelTemplateNew.replace("{MODELHIDDENNAME}",modelNameAutoComplete);
	ModelTemplateNew = ModelTemplateNew.replace("modelChangeModelCode[]","modelGenerateCode[" + counterModel + "]");
	ModelTemplateNew = ModelTemplateNew.replace("modelChangeModelOriginalName[]","modelGenerateOriginalName[" + counterModel + "]");
	ModelTemplateNew = ModelTemplateNew.replace("modelChangeActionName[]","modelGenerateAction[" + counterModel + "]");
	ModelTemplateNew = ModelTemplateNew.replace("modelChangeVariableName[]","modelGenerateVariableName[" + counterModel + "]");
	ModelTemplateNew = ModelTemplateNew.replace("{MODELVARIABLENAME}",modelNameAutoComplete + (counterModel == 0 ? "" : counterModel));
	$(".Models").append(ModelTemplateNew);
	counterModel++;
	jQuery(\'.ModelCode\').autocomplete({"source":' . json_encode($ModelCode) . '});
    }else{
	alert("You have to write a Model Name.");
    }
});
$(document).on("click",".removeModel",function(){
    $(this).closest(".ModelList").remove();
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
			    <?php echo Html::input('text', 'ControllerNameChange[]', $MainControllerName, ['class' => 'form-control']); ?><hr />
			    <?php echo Html::label('Actions:'); ?>
			    <?php echo Html::dropDownList('ControllerActionChange[]', $ActionList, $ActionList, ['class' => 'form-control', 'multiple' => true, 'size' => 6]); ?>
    		    </div>
    		    <div class="panel-footer"><?php echo Html::button('Remove', ['class' => 'btn btn-danger removeController']); ?></div>
    		</div>
    	    </div>
    	</div>
	    <?php echo Html::beginForm(); ?>
    	<hr/>


    	<div class="panel panel-default">
    	    <div class="panel-heading">Controllers:</div>
    	    <div class="panel-body">
    		<div class="row Controllers">
    		    <div class="col-md-4 ControllerList">
    			<div class="panel panel-default">
    			    <div class="panel-body">
				    <?php echo Html::label('Controller Name:'); ?>
				    <?php echo Html::input('text', 'controllerName[0]', $MainControllerName, ['class' => 'form-control']); ?><hr />
				    <?php echo Html::label('Actions:'); ?>
				    <?php echo Html::dropDownList('controllerAction[0][]', $ActionList, $ActionList, ['class' => 'form-control', 'multiple' => true, 'size' => 6]); ?>
    			    </div>
    			    <div class="panel-footer"><?php echo Html::button('Remove', ['class' => 'btn btn-danger removeController']); ?></div>
    			</div>
    		    </div>
    		</div>
    	    </div>
    	    <div class="panel-footer"><?php echo Html::button('Add Controller', ['class' => 'btn btn-success addController']); ?></div>
    	</div>

    	<div class="panel panel-default">
    	    <div class="panel-heading">Models:</div>
    	    <div class="panel-body">
    		<div class="ModelListTemplate" style="display:none;">
    		    <div class="col-md-4 ModelList">
    			<div class="panel panel-default">
    			    <div class="panel-body">
				    <?php echo Html::label('{ACTIONNAME}'); ?>
				    <?php echo Html::input('hidden', 'modelChangeActionName[]', '{ACTIONHIDDENNAME}'); ?>
				    <?php echo Html::input('hidden', 'modelChangeModelOriginalName[]', '{MODELHIDDENNAME}'); ?>
    				<hr />
				    <?php echo Html::label('Variable Name:'); ?>
				    <?php echo Html::input('text', 'modelChangeVariableName[]', '{MODELVARIABLENAME}', ['class' => 'form-control']); ?>
    				<hr />
				    <?php echo Html::label('Model Code:'); ?>
				    <?php echo Html::input('text', 'modelChangeModelCode[]', 'find()', ['class' => 'form-control ModelCode ui-autocomplete-input','autocomplete' => 'off']); ?>
    			    </div>
    			    <div class="panel-footer"><?php echo Html::button('Remove', ['class' => 'btn btn-danger removeModel']); ?></div>
    			</div>
    		    </div>
    		</div>

    		<div class="row Models">

    		</div>
    	    </div>
    	    <div class="panel-footer">
    		<div class="row">
    		    <div class="col-md-6">
			    <?php echo Html::label('Model Name:'); ?>
			    <?php
			    echo AutoComplete::widget([
				'name' => 'modelNameAutoComplete',
				'id' => 'modelNameAutoComplete',
				'clientOptions' => [
				    'source' => $modelList,
				],
				'options' => ['class' => 'form-control']
			    ]);
			    ?>
    		    </div>
    		    <div class="col-md-6">
			    <?php echo Html::label('Actions:'); ?>
			    <?php echo Html::dropDownList('modelGenerateActionSelect', $ActionList, $ActionList, ['id' => 'modelGenerateAction', 'class' => 'form-control']); ?>
    		    </div>
    		</div>
    		<hr />
		    <?php echo Html::button('Add Model', ['class' => 'btn btn-success addModel']); ?>
    	    </div>
    	</div>
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
			    <?php echo Html::label('Which file will use for layout?:'); ?>
			    <?php echo Html::dropDownList('file', null, $fileList, ['class' => 'form-control']); ?>
    		    </div>
    		    <div class="panel-footer"><?php echo Html::submitButton('Generate', ['class' => 'btn btn-success']); ?></div>
    		</div>
    	    </div>
    	</div>
	    <?php echo Html::input('hidden', 'folder', $folder); ?>
	    <?php echo Html::input('hidden', 'step', '2'); ?>
	    <?php echo Html::endForm(); ?>
	    <?php
	} else {
	    echo '<div class="alert alert-danger">Couldn\'t find a file.</div>';
	}
	?>
    </div>
</div>
<?php $this->endContent(); ?>
