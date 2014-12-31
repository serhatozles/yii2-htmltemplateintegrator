<?php

use kartik\helpers\Html;
use yii\helpers\Url;
use yii\jui\AutoComplete;
use yii\bootstrap\Modal;

$ModelCode = [
    "find()",
    "findAll([])",
    "findBySql('YOURQUERY')",
    "findOne()",
    'find()->where([])',
];

$js = 'window.replaceAll = function (find, replace, str) { return str.replace(new RegExp(find, \'g\'), replace); };';
$js .= 'window.general = ' . json_encode($generalVariable) . ';';

$this->registerJs($js, \yii\web\View::POS_READY);

$js = '
var ModelTemplate = $(".ModelListTemplate").html();

var counterModel = 0;
var htiSelectorList = Array();
var htiHtmlList = Array();
var htiHtmlSelectList = Array();
$(document).on("click",".addModel",function(){
    var modelNameAutoComplete = $("#modelNameAutoComplete").val();
    var modelGenerateAction = $("#modelGenerateAction option:selected").text();
    var modelActionFile = $("#modelGenerateAction").val();
    var modelWebAdress = window.general["webTemplateAddress"];
    var contentSelector = $("input[name=contentselector]").val();
//    $("#modelNameAutoComplete").val("");

    if(modelNameAutoComplete != ""){
	var ModelTemplateNew = window.replaceAll("{ACTIONNAME}",modelGenerateAction,ModelTemplate);
	ModelTemplateNew = window.replaceAll("{MODELNAME}",modelNameAutoComplete,ModelTemplateNew);
	ModelTemplateNew = window.replaceAll("{ACTIONIFRAMESRC}",modelActionFile + "&contentSelector=" + contentSelector,ModelTemplateNew);
//	ModelTemplateNew = window.replaceAll("{ACTIONIFRAMESRC}",modelWebAdress + modelActionFile + "&contentSelector=" + contentSelector,ModelTemplateNew);
	ModelTemplateNew = window.replaceAll("{MODALTITLE}","<strong>Action Name:</strong> " + modelGenerateAction + " | <strong>File Name:</strong> " + modelActionFile,ModelTemplateNew);
	ModelTemplateNew = ModelTemplateNew.replace("modelChangeModel[]","modelGenerateModelID[" + counterModel + "]");
	ModelTemplateNew = ModelTemplateNew.replace("modelChangeModelCode[]","modelGenerateCode[" + counterModel + "]");
	ModelTemplateNew = ModelTemplateNew.replace("modelChangeModelOriginalName[]","modelGenerateOriginalName[" + counterModel + "]");
	ModelTemplateNew = ModelTemplateNew.replace("modelChangeActionVariables[]","modelGenerateActionVariables[" + counterModel + "]");
	ModelTemplateNew = ModelTemplateNew.replace("modelChangeActionName[]","modelGenerateAction[" + counterModel + "]");
	ModelTemplateNew = ModelTemplateNew.replace("modelChangeVariableName[]","modelGenerateVariableName[" + counterModel + "]");
	ModelTemplateNew = window.replaceAll("{MODELVARIABLENAME}",modelNameAutoComplete + (counterModel == 0 ? "" : counterModel),ModelTemplateNew);
	
	var ModalID = "Modal" + counterModel;
	var ModalIframeID = "ModalIframe" + counterModel;
	
	ModelTemplateNew = window.replaceAll("{MODALID}","Modal" + counterModel,ModelTemplateNew);
	ModelTemplateNew = window.replaceAll("{MODALIFRAMEID}","ModalIframe" + counterModel,ModelTemplateNew);
	
	$(".Models").append(ModelTemplateNew);

	$(".modelCoderSelector").on("keypress", function(e) {
	    var code = e.keyCode || e.which; 
	    if (code  == 13) {
	    var $selectHtml = "";
	    var $selectorVar = $(this).val();
	    try{
		$selectHtml = $("#" + ModalIframeID).contents().find($selectorVar).html();
	    }
	    catch(err) {
		alert("Object is not found.");
	    }
	    if($selectHtml != "" && typeof $selectHtml != "undefined"){
		$modelCoderEditor.getSession().setValue($selectHtml);
		$("#" + ModalIframeID).get(0).contentWindow.htiTagSelector = $selectorVar;
		$("#" + ModalIframeID).get(0).contentWindow.htiTagHtml = $selectHtml;
		var newSelector = $("#" + ModalIframeID).contents().find($selectorVar);
		var position = newSelector.offset();
		$("#" + ModalIframeID).contents().find(".HtmlTemplateIntegratorSelected").css("width", newSelector.outerWidth());
		$("#" + ModalIframeID).contents().find(".HtmlTemplateIntegratorSelected").css("height", newSelector.outerHeight());
		$("#" + ModalIframeID).contents().find(".HtmlTemplateIntegratorSelected").css("left", position.left);
		$("#" + ModalIframeID).contents().find(".HtmlTemplateIntegratorSelected").css("top", position.top);
		$("#" + ModalIframeID).contents().find(".HtmlTemplateIntegratorSelected").show();
	    }else{
		$(this).val($("#" + ModalIframeID).get(0).contentWindow.htiTagSelector);
	    }
		e.preventDefault();
		return false;
	    }
	});

//	var $modelCoderEditor = $("#" + ModalID + "modelCoder").ace({ theme: "twilight", lang: "php",width: "100%",height:250 });
	var $modelCoderEditorTextArea = $("#" + ModalID + "modelCoder");
//	var $modelCoderAutoCodeEditor = $("#" + ModalID + "modelCoderAutoCode").ace({ theme: "twilight", lang: "php",width: "100%",height:250 });
	var $modelCoderAutoCodeTextArea = $("#" + ModalID + "modelCoderAutoCode");
	
	var $modelCoderEditor = ace.edit(ModalID + "modelCoderEditor");
	$modelCoderEditorTextArea.hide();
	$modelCoderEditor.setTheme("ace/theme/twilight");
	$modelCoderEditor.getSession().setMode("ace/mode/php");
	$modelCoderEditor.getSession().setValue($modelCoderEditorTextArea.val());
	$modelCoderEditor.getSession().on("change", function(){
	  $modelCoderEditorTextArea.val($modelCoderEditor.getSession().getValue());
	});
	
	var $modelCoderAutoCodeEditor = ace.edit(ModalID + "modelCoderAutoCodeEditor");
	$modelCoderAutoCodeTextArea.hide();
	$modelCoderAutoCodeEditor.setTheme("ace/theme/twilight");
	$modelCoderAutoCodeEditor.getSession().setMode("ace/mode/php");
	$modelCoderAutoCodeEditor.getSession().setValue($modelCoderAutoCodeTextArea.val());
	$modelCoderAutoCodeEditor.getSession().on("change", function(){
	  $modelCoderAutoCodeTextArea.val($modelCoderAutoCodeEditor.getSession().getValue());
	});
	
	$("#Modal" + counterModel + "Button").on("click", function () {
	
	    var $btn = $(this).button("loading");
	    
	    var $selector = $(this).closest(".modal-content").find("input.modelCoderSelector").val();
	    var $selectorHtml = $(this).closest(".modal-content").find("textarea.modelCoder").val();

	    if($selector != "" && $selectorHtml != ""){

		$.ajax({
		    type: "POST",
		    url: "' . Url::to(['integrator/save', 'folder' => $folder,]) . '&modelname=" + ModalID + "&file=" + modelActionFile + "&selector=" + $selector,
		    data: {html:$selectorHtml},
		}).done(function(result) {
		    $("#" + ModalID).modal("hide");
		    $btn.button("reset");
		});
	    
	    }else{
	    
		alert("You should select any element.");
		$btn.button("reset");
	    
	    }
	    
	});
	
	$("#ModalIframe" + counterModel).load(function() {
	
	    var $modalid = $(this).attr("id");
	
	    htiSelectorList[ $modalid ] = "";
	    htiHtmlList[ $modalid ] = "";
	    htiHtmlSelectList[ $modalid ] = "";
	    
	    setInterval(function(){
	    
		var $selector = $("#" + $modalid).get(0).contentWindow.htiTagSelector;
		var $selectorHtml = $("#" + $modalid).get(0).contentWindow.htiTagHtml;
		var $selectorAutoCode = $("#" + $modalid).parent().find("select.modelCoderAutoCodeSelect").val();
		
		if($selectorAutoCode != htiHtmlSelectList[ $modalid ]){
		
		    var $ModelAutoCode = "";
		    
		    if($selectorAutoCode == "Many"){
		    
			$ModelAutoCode += "<?php foreach({MODELVARIABLENAME} as {MODELVARIABLENAME}List) : ?>" + "\r\n";
		    
			$.each(window.general.modelList[modelNameAutoComplete], function(index, value) {

			    $ModelAutoCode += "<?php echo {MODELVARIABLENAME}List->" + index + "; /* "+ value + " */ ?>\r\n";

			});
			
			$ModelAutoCode += "<?php endforeach; ?>" + "\r\n";
		    
		    }else if($selectorAutoCode == "One"){
		    
			$.each(window.general.modelList[modelNameAutoComplete], function(index, value) {

			    $ModelAutoCode += "<?php echo {MODELVARIABLENAME}->" + index + "; /* " + value + " */ ?>\r\n";

			});

		    }else if($selectorAutoCode == "Form"){
		    
			$ModelAutoCode += "<?php ${MODELVARIABLENAME}Form = ActiveForm::begin([\"id\" => \"{MODELVARIABLENAME}Form\"]); ?>" + "\r\n";
		    
			$.each(window.general.modelList[modelNameAutoComplete], function(index, value) {

			    $ModelAutoCode += "<?php echo ${MODELVARIABLENAME}Form->field({MODELVARIABLENAME}, \"" + index + "\"); ?>\r\n";
			    $ModelAutoCode += "<?php echo ${MODELVARIABLENAME}Form->field({MODELVARIABLENAME}, \"" + index + "\")->textArea([\"rows\" => 6]); ?>\r\n";

			});
			
			$ModelAutoCode += "<?php ActiveForm::end(); ?>" + "\r\n";
		    
		    }
		    
		    $("#" + $modalid).parent().find("textarea.modelCoderAutoCode").val($ModelAutoCode);
		    
		    $modelCoderAutoCodeEditor.getSession().setValue($modelCoderAutoCodeTextArea.val());
		    
		    htiHtmlSelectList[ $modalid ] = $selectorAutoCode;

		}
		
		if(htiSelectorList[ $modalid ] != $selector){
		
		    htiSelectorList[ $modalid ] = $selector;
		    htiHtmlList[ $modalid ] = $selectorHtml;
		    
//		    $.ajax({
//			url: "' . Url::to(['integrator/include', 'folder' => $folder,]) . '&file=" + modelActionFile + "&selector=" + $selector,
//		    }).done(function(result) {
//			alert($selector);
			$("#" + $modalid).parent().find("input.modelCoderSelector").val($selector);
			$("#" + $modalid).parent().find("textarea.modelCoder").val($selectorHtml);
			
			$modelCoderEditor.getSession().setValue($modelCoderEditorTextArea.val());
			
//		    });

		}
	    
	    },500);

	});
//	alert($("#ModalIframe0").get(0).contentWindow.htiTagSelector);

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

//$js = '
//    
//';
//$this->registerJs($js, \yii\web\View::POS_READY);
//$js = '$(".ModelCode").textcomplete([
//    { // tech companies
//        words: ["apple", "google", "facebook", "github"],
//        match: /\b(\w{2,})$/,
//        search: function (term, callback) {
//            callback($.map(this.words, function (word) {
//                return word.indexOf(term) === 0 ? word : null;
//            }));
//        },
//        index: 1,
//        replace: function (word) {
//            return word + " ";
//        }
//    }
//]);';
//$this->registerJs($js, \yii\web\View::POS_READY);

$this->beginContent(__DIR__ . '/layouts/main.php');
?>
<div class="row">
    <div class="col-md-12 text-center">


	<?php
	echo '<h5>Firstly, you have to put your template folder into @app/template. ' . Html::bsLabel('Required', Html::TYPE_DANGER) . '</h5>';

	if (count($fileList) > 0) {
	    ?>
	    <?php echo Html::beginForm(); ?>
    	<hr/>

    	<div class="panel panel-default">
    	    <div class="panel-heading">Models:</div>
    	    <div class="panel-body">
    		<div class="ModelListTemplate" style="display:none;">
    		    <div class="col-md-4 ModelList">
    			<div class="panel panel-default">
    			    <div class="panel-body">
    				<div class="row">
    				    <div class="col-md-6">
					    <?php echo Html::label('Action Name:'); ?><br />
    					{ACTIONNAME}
    				    </div>
    				    <div class="col-md-6">
					    <?php echo Html::label('Model Name:'); ?><br />
    					{MODELNAME}
    				    </div>
    				</div>
				    <?php echo Html::input('hidden', 'modelChangeModel[]', '{MODALID}'); ?>
				    <?php echo Html::input('hidden', 'modelChangeActionName[]', '{ACTIONNAME}'); ?>
				    <?php echo Html::input('hidden', 'modelChangeModelOriginalName[]', '{MODELNAME}'); ?>
    				<hr />
				    <?php echo Html::label('Action Variables(Exp:$id,$url):'); ?>
				    <?php echo Html::input('text', 'modelChangeActionVariables[]', '', ['class' => 'form-control']); ?>
    				<hr />
				    <?php echo Html::label('Variable Name:'); ?>
				    <?php echo Html::input('text', 'modelChangeVariableName[]', '{MODELVARIABLENAME}', ['class' => 'form-control']); ?>
    				<hr />
				    <?php echo Html::label('Model Code:'); ?>
				    <?php echo Html::input('text', 'modelChangeModelCode[]', 'find()', ['class' => 'form-control ModelCode ui-autocomplete-input', 'autocomplete' => 'off']); ?>
    			    </div>
    			    <div class="panel-footer">
				    <?php echo Html::button('Choose Into HTML (Beta)', ['class' => 'btn btn-danger chooseModel', 'data-toggle' => 'modal', 'data-target' => '#{MODALID}']); ?>
				    <?php echo Html::button('Remove', ['class' => 'btn btn-danger removeModel']); ?>
    			    </div>
    			</div>

    			<!-- Modal -->
    			<div class="modal fade" id="{MODALID}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    			    <div class="modal-dialog" style="width:98%;">
    				<div class="modal-content">
    				    <div class="modal-header">
    					<h4 class="modal-title" id="myModalLabel">{MODALTITLE}</h4>
    				    </div>
    				    <div class="modal-body">
    					<iframe id="{MODALIFRAMEID}" class="ModalIframe" src="<?php echo Url::to(['integrator/include', 'folder' => $folder]); ?>&file={ACTIONIFRAMESRC}" style="width:100%;height:300px;border:1px solid #000;"></iframe>

    					<hr />

    					<div class="row">
    					    <div class="col-md-6">
						    <?php echo Html::label('Selector:'); ?>
						    <?php echo Html::input('text', '', '', ['class' => 'form-control modelCoderSelector']); ?>
    						<hr />
						    <?php echo Html::label('Selector HTML:'); ?>
    						<div class="text-left" style="height:250px;">
							<?php echo Html::textarea('', '', ['class' => 'form-control modelCoder', 'id' => '{MODALID}modelCoder']); ?>
							<div id="{MODALID}modelCoderEditor" style="width:95%;height:250px;display:block;"></div>
    						</div>
    					    </div>
    					    <div class="col-md-6">
						    <?php echo Html::label('Helper Code Type:'); ?>
						    <?php echo Html::dropDownList('', null, ['Many' => 'Many', 'One' => 'One', 'Form' => 'Form'], ['class' => 'form-control modelCoderAutoCodeSelect']); ?>
    						<hr />
						    <?php echo Html::label('Helper Code List:'); ?>
    						<div class="text-left" style="height:250px;">
							<?php echo Html::textarea('', '', ['class' => 'form-control modelCoderAutoCode', 'id' => '{MODALID}modelCoderAutoCode']); ?>
						    <div id="{MODALID}modelCoderAutoCodeEditor" style="width:95%;height:250px;display:block;"></div>
    						</div>
    					    </div>
    					</div>
					
    						<!--<iframe id="{MODALIFRAMEID}" class="ModalIframe" src="{ACTIONIFRAMESRC}" style="width:100%;height:500px;border:1px solid #000;"></iframe>-->
    				    </div>
    				    <div class="modal-footer">
    					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    					<button type="button" class="btn btn-primary" id="{MODALID}Button" data-loading-text="Saving...">Save</button>
    				    </div>
    				</div>
    			    </div>
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
			    AutoComplete::widget([
				'name' => 'modelNameAutoComplete',
				'id' => 'modelNameAutoComplete',
				'clientOptions' => [
				    'source' => $generalVariable['modelListName'],
				],
				'options' => ['class' => 'form-control']
			    ]);
			    ?>
			    <?php echo Html::dropDownList('modelNameAutoComplete', null, $generalVariable['modelListName'], ['id' => 'modelNameAutoComplete', 'class' => 'form-control']); ?>
    		    </div>
    		    <div class="col-md-6">
			    <?php echo Html::label('Actions:'); ?>
			    <?php echo Html::dropDownList('modelGenerateActionSelect', null, $ActionList, ['id' => 'modelGenerateAction', 'class' => 'form-control']); ?>
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
			<?php echo '<h5>Firstly, you have to put your template folder into @app/template. ' . Html::bsLabel('Required', Html::TYPE_DANGER) . '</h5>'; ?>
    		    </div>
    		</div>
    	    </div>
    	    <div class="col-md-6 text-center">
    		<div class="panel panel-default">
    		    <div class="panel-body">
			<?php echo Html::submitButton('Generate', ['class' => 'btn btn-success']); ?>
    		    </div>
    		</div>
    	    </div>
    	</div>
	    <?php echo Html::input('hidden', 'headerselector', $headerSelector); ?>
	    <?php echo Html::input('hidden', 'contentselector', $contentSelector); ?>
	    <?php echo Html::input('hidden', 'footerselector', $footerSelector); ?>
	    <?php echo Html::input('hidden', 'controllerList', json_encode($controllerList)); ?>
	    <?php echo Html::input('hidden', 'createAssets', $createAssets); ?>
	    <?php echo Html::input('hidden', 'createLayouts', $createLayouts); ?>
	    <?php echo Html::input('hidden', 'file', $file); ?>
	    <?php echo Html::input('hidden', 'folder', $folder); ?>
	    <?php echo Html::input('hidden', 'step', '3'); ?>
	    <?php echo Html::endForm(); ?>
	    <?php
	} else {
	    echo '<div class="alert alert-danger">Couldn\'t find a file.</div>';
	}
	?>
    </div>
</div>
<?php $this->endContent(); ?>
