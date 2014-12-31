<?php

use kartik\helpers\Html;
use yii\helpers\Url;
use yii\jui\AutoComplete;
use yii\bootstrap\Modal;

$js = 'window.replaceAll = function (find, replace, str) { return str.replace(new RegExp(find, \'g\'), replace); };';
$js .= 'window.general = ' . json_encode($generalVariable) . ';';

$this->registerJs($js, \yii\web\View::POS_READY);

$js = '
var ControllerTemplate = $(".ControllerTemplate").html();

var counter = 0;
var ControllerName = "' . $MainControllerName . '";
$(document).on("click",".addController",function(){
    counter++;
    var ControllerTemplateNew = ControllerTemplate.replace("ControllerNameChange[]","ControllerList[" + counter + "][Name]");
    ControllerTemplateNew = ControllerTemplateNew.replace("{CONTROLLERID}",counter);
//    ControllerTemplateNew = ControllerTemplateNew.replace("ControllerActionChange[]","controllerAction[" + counter + "][]");
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
$(document).on("click",".removeAction",function(){
    $(this).closest(".ActionItem").remove();
});
$(document).on("click",".addAction",function(){
    var $ActionName = $(this).closest(".panel-footer").find("input[name=controllerActionAddName]").val();
    var $ActionFile = $(this).closest(".panel-footer").find("select[name=controllerAddFile]").val();
    var $ControllerID = $(this).closest(".ControllerList").data("id");
    
    if(!($(this).closest(".panel").find(".panel-body .controllerActionName[value=" + $ActionName + "]").length > 0)){
    
	if($ActionName != ""){
	    $Action = $(".ControllerActionTemplate").html();

	    $Action = window.replaceAll("{ACTIONNAME}",$ActionName,$Action);
	    $Action = window.replaceAll("{ACTIONFILE}",$ActionFile,$Action);
	    $Action = window.replaceAll("{ACTIONGENFILE}",window.general["ActionGenList"][$ActionFile],$Action);
	    $Action = $Action.replace("controllerActionName[]","ControllerList[" + $ControllerID + "][ActionList][]");
	    $Action = $Action.replace("controllerActionFileName[]","ControllerList[" + $ControllerID + "][ActionFileName][]");
	    $Action = $Action.replace("controllerActionFileGenName[]","ControllerList[" + $ControllerID + "][ActionFileGenName][]");

	    $(this).closest(".panel").find(".ControllerActionList").append($Action);
	}else{
	    alert("You have to give a name for action!");
	}
	
    }else{
	alert("There is another action with the same name.");
    }
});

$(document).on("change", ".controllerActionAddName", function(){
    str = $(this).val();
    str = str.replace(/\b[a-z]/g, function(letter) {
	return letter.toUpperCase();
    });
    str = str.replace(/\s+/g, "");
    $(this).val(str);
});
    ';
$this->registerJs($js, \yii\web\View::POS_READY);

$this->registerCss(".ControllerActionList {height:250px;max-height:250px;overflow-y:auto;}");

$this->beginContent(__DIR__ . '/layouts/main.php');
?>
<div class="row">
    <div class="col-md-12 text-center">


	<?php
	echo '<h5>Firstly, you have to put your template folder into @app/template. ' . Html::bsLabel('Required', Html::TYPE_DANGER) . '</h5>';

	if (count($fileList) > 0) {
	    ?>
    	<div class="ControllerTemplate" style="display:none;">
    	    <div class="col-md-4 ControllerList" data-id="{CONTROLLERID}">
    		<div class="panel panel-default">
    		    <div class="panel-body">
			    <?php echo Html::label('Controller Name:'); ?>
			    <?php echo Html::input('text', 'ControllerNameChange[]', $MainControllerName, ['class' => 'form-control']); ?><hr />
			    <?php echo Html::label('Actions:'); ?>
    			<div class="panel panel-default">
    			    <div class="panel-body ControllerActionList">

				

    			    </div>
    			    <div class="panel-footer">
				    <?php echo Html::input('text', 'controllerActionAddName', '', ['class' => 'form-control controllerActionAddName']); ?><br />
    				<div class="row">
    				    <div class="col-md-7">
					    <?php echo Html::dropDownList('controllerAddFile', $fileList, $fileList, ['class' => 'form-control']); ?>
    				    </div>
    				    <div class="col-md-5 text-right">
					    <?php echo Html::button('Action Add', ['class' => 'btn btn-success addAction']); ?>
    				    </div>
    				</div>
    			    </div>
    			</div>
    		    </div>
    		    <div class="panel-footer"><?php echo Html::button('Remove', ['class' => 'btn btn-danger removeController']); ?></div>
    		</div>
    	    </div>
    	</div>
    	<div class="ControllerActionTemplate" style="display:none;">
    	    <div class="panel panel-default ActionItem" style="background-color:#F5F5F5;">
    		<div class="panel-body">
    		    <div class="row">
    			<div class="col-md-3">
    			    <strong>Name:</strong><br />
    			    {ACTIONNAME}
				<?php echo Html::input('hidden', 'controllerActionName[]', "{ACTIONNAME}",['class' => 'controllerActionName']); ?>
    			</div>
    			<div class="col-md-4">
    			    <strong>File:</strong><br />
    			    {ACTIONFILE}
				<?php echo Html::input('hidden', 'controllerActionFileName[]', "{ACTIONFILE}"); ?>
				<?php echo Html::input('hidden', 'controllerActionFileGenName[]', "{ACTIONGENFILE}"); ?>
    			</div>
    			<div class="col-md-5 text-right">
				<?php echo Html::button('Remove', ['class' => 'btn btn-danger removeAction']); ?>
    			</div>
    		    </div>
    		</div>
    	    </div>
    	</div>
	    <?php echo Html::beginForm(); ?>
    	<hr/>


    	<div class="panel panel-default">
    	    <div class="panel-heading">Controllers:</div>
    	    <div class="panel-body">
    		<div class="row Controllers">
    		    <div class="col-md-4 ControllerList" data-id="0">
    			<div class="panel panel-default">
    			    <div class="panel-body">
				    <?php echo Html::label('Controller Name:'); ?>
				    <?php echo Html::input('text', 'ControllerList[0][Name]', $MainControllerName, ['class' => 'form-control']); ?><hr />
				    <?php echo Html::label('Actions:'); ?>
    				<div class="panel panel-default">
    				    <div class="panel-body ControllerActionList">



    				    </div>
    				    <div class="panel-footer">
					    <?php echo Html::input('text', 'controllerActionAddName', '', ['class' => 'form-control controllerActionAddName']); ?><br />
    					<div class="row">
    					    <div class="col-md-7">
						    <?php echo Html::dropDownList('controllerAddFile', $fileList, $fileList, ['class' => 'form-control']); ?>
    					    </div>
    					    <div class="col-md-5 text-right">
						    <?php echo Html::button('Action Add', ['class' => 'btn btn-success addAction']); ?>
    					    </div>
    					</div>
    				    </div>
    				</div>
    			    </div>
    			    <div class="panel-footer">
				    <?php echo Html::button('Remove', ['class' => 'btn btn-danger removeController']); ?>
    			    </div>
    			</div>
    		    </div>
    		</div>
    	    </div>
    	    <div class="panel-footer"><?php echo Html::button('Add Controller', ['class' => 'btn btn-success addController']); ?></div>
    	</div>

    	<div class="row">
    	    <div class="col-md-6 text-center">
    		<div class="panel panel-default">
    		    <div class="panel-body">
			    <?php echo '<h5>Firstly, you have to put your template folder into @app/template. ' . Html::bsLabel('Required', Html::TYPE_DANGER) . '</h5>'; ?>
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
	    <?php echo Html::input('hidden', 'headerselector', $headerSelector); ?>
	    <?php echo Html::input('hidden', 'contentselector', $contentSelector); ?>
	    <?php echo Html::input('hidden', 'footerselector', $footerSelector); ?>
	    <?php echo Html::input('hidden', 'createAssets', $createAssets); ?>
	    <?php echo Html::input('hidden', 'createLayouts', $createLayouts); ?>
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
