<?php

namespace serhatozles\themeintegrator;

/*
 * 
 * @author: Serhat ÖZLEŞ
 * @email: serhatozles@gmail.com
 * @Url: https://github.com/serhatozles/yii2-htmltemplateintegrator
 * 
 */

use Yii;
use yii\helpers\Json;
use serhatozles\simplehtmldom\SimpleHTMLDom;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\helpers\Enum;
use yii\web\Controller as BaseController;
use yii\caching\FileCache;

include(__DIR__ . '/ganon/ganon.php');
include(__DIR__ . '/ganon/third party/jsminplus.php');

set_time_limit(0);

class Controller extends BaseController {

    public $general = [];
    public $layoutsFirstList = [];
    public $layoutsList = [];
    public $layoutsListAll = [];
    public $assetsList = [];
    public $list = [];
    public $listOriginal = [];
    private $assetTemplate = '/template/Asset.txt';
    private $layoutTemplate = '/template/Layout.txt';
    private $controllerTemplate = '/template/Controller.txt';
    private $assetGeneral = 'General';
    private $layoutGeneral = 'general';
    public $generatedFiles = [];
    public $appname = "";
    public $templatePath = "";

    const ACTIONNAME = "ACTIONNAME";
    const ACTIONFILENAME = "ACTIONFILENAME";
    const ACTIONLAYOUT = "ACTIONLAYOUT";
    const ACTIONMODELS = "ACTIONMODELS";
    const ACTIONVARIABLES = "ACTIONVARIABLES";
    const ACTIONMODELSVARIABLES = "ACTIONMODELSVARIABLES";
    const ASSETNAME = "ASSETNAME";
    const ASSETCSSLIST = "ASSETCSSLIST";
    const ASSETJSLIST = "ASSETJSLIST";
    const ASSETFOLDER = "ASSETFOLDER";
    const APPNAME = "APPNAME";
    const ASSETFILESLIST = "ASSETFILESLIST";
    const LAYOUTASSETSUSE = "LAYOUTASSETSUSE";
    const LAYOUTASSETSREGISTER = "LAYOUTASSETSREGISTER";
    const LAYOUTFILESLIST = "LAYOUTFILESLIST";
    const CONTROLLERNAME = "CONTROLLERNAME";
    const CONTROLLERMODELS = "CONTROLLERMODELS";
    const CONTROLLERACTIONLIST = "CONTROLLERACTIONLIST";

    public $actionTemplate = '
    /**
    * @actionName: {ACTIONNAME}
    * @layout: {ACTIONLAYOUT}
    */
    public function action{ACTIONNAME}({ACTIONVARIABLES}) {
	$this->layout = "{ACTIONLAYOUT}";
	
{ACTIONMODELS}
	return $this->render("{ACTIONFILENAME}",[{ACTIONMODELSVARIABLES}]);
    }';
    public $headerSelector = '';
    public $contentSelector = '';
    public $footerSelector = '';
    public $layoutSource = '';
    public $layoutSourceFirst = '';
    public $folderName = null;
    public $urlReplace = [];

    function init() {
	$this->appname = str_replace('app-', '', Yii::$app->id);
	$this->templatePath = Yii::getAlias('@app/template/');
	$this->folderCreate($this->templatePath);
    }

    public function actionDefine() {

	HTMLIntegratorBootstrapAsset::register($this->getView());
	if (Yii::$app->request->isPost) {

	    $post = Yii::$app->request->post();

	    $folderName = $post['folder'];
	    $folder = $this->templatePath . $post['folder'];
	    $fileList = $this->getHtml($folder);

	    if ($post['step'] == 1) {

		$headerSelector = $post['headerselector'];
		$contentSelector = $post['contentselector'];
		$footerSelector = $post['footerselector'];

		$ActionList = [];
		$generalVariable = [];

		for ($i = 0; $i < count($fileList); $i++):

		    $filename = pathinfo($fileList[$i]);
		    $genfilename = $this->nameGenerator($filename['filename']);

		    if ($this->validatesAsInt($genfilename[0])) {
			$genfilename = 'Html' . $genfilename;
		    }

		    $ActionList[] = $genfilename;
		    $generalVariable['ActionList'][$genfilename] = $fileList[$i];
		    $generalVariable['ActionGenList'][$fileList[$i]] = $genfilename;

		endfor;

		$generalVariable['webTemplateAddress'] = Yii::getAlias('@web/../template/' . $folderName . '/');

		$fileList = array_combine($fileList, $fileList);

		$MainControllerName = $this->nameGenerator($folderName);

		return $this->renderFile(__DIR__ . "/views/controller.php", [
			    'fileList' => $fileList,
			    'folder' => $folderName,
			    'MainControllerName' => $MainControllerName,
			    'generalVariable' => $generalVariable,
			    'headerSelector' => $headerSelector,
			    'contentSelector' => $contentSelector,
			    'footerSelector' => $footerSelector,
		]);
	    } elseif ($post['step'] == 2) {

		HTMLIntegratorAsset::register($this->getView());

		$modelList = $this->getModels();
		$headerSelector = $post['headerselector'];
		$contentSelector = $post['contentselector'];
		$footerSelector = $post['footerselector'];
		$file = $post['file'];
		$controllerList = $post['ControllerList'];

		$ActionList = [];
		$generalVariable = [];

		foreach ($controllerList as $ControllerKey => $ControllerInfo):

		    foreach ($ControllerInfo['ActionList'] as $ControllerActionKey => $ControllerAction):
			$ActionList[$ControllerInfo['ActionFileName'][$ControllerActionKey]] = $ControllerInfo['Name'] . '>' . $ControllerAction;
		    endforeach;

		endforeach;

		for ($i = 0; $i < count($fileList); $i++):

		    $filename = pathinfo($fileList[$i]);
		    $genfilename = $this->nameGenerator($filename['filename']);

		    if ($this->validatesAsInt($genfilename[0])) {
			$genfilename = 'Html' . $genfilename;
		    }

//		    $ActionList[] = $genfilename;
		    $generalVariable['ActionList'][$genfilename] = $fileList[$i];

		endfor;

		$generalVariable['webTemplateAddress'] = Yii::getAlias('@web/../template/' . $folderName . '/');
		$generalVariable['modelList'] = ArrayHelper::map($modelList, 'ModelName', 'ModelAttr');
		$generalVariable['modelListName'] = ArrayHelper::map($modelList, 'ModelName', 'ModelName');

//		$ActionList = array_combine($ActionList, $ActionList);
		$fileList = array_combine($fileList, $fileList);

		$MainControllerName = $this->nameGenerator($folderName);

		return $this->renderFile(__DIR__ . "/views/file.php", [
			    'fileList' => $fileList,
			    'folder' => $folderName,
			    'ActionList' => $ActionList,
			    'MainControllerName' => $MainControllerName,
			    'generalVariable' => $generalVariable,
			    'headerSelector' => $headerSelector,
			    'contentSelector' => $contentSelector,
			    'footerSelector' => $footerSelector,
			    'controllerList' => $controllerList,
			    'file' => $file,
		]);
	    } elseif ($post['step'] == 3) {

		$fileName = $post['file'];
		$this->folderName = $folderName;
		$this->headerSelector = $post['headerselector'];
		$this->contentSelector = $post['contentselector'];
		$this->footerSelector = $post['footerselector'];
		$controllerName = $post['controllerName'];
		$controllerAction = $post['controllerAction'];
		$modelGenerateAction = $post['modelGenerateAction'];
		$modelGenerateOriginalName = $post['modelGenerateOriginalName'];
		$modelGenerateCode = $post['modelGenerateCode'];
		$modelGenerateVariableName = $post['modelGenerateVariableName'];
		$modelGenerateModelID = $post['modelGenerateModelID'];
		$modelGenerateActionVariables = $post['modelGenerateActionVariables'];
		$controllerList = json_decode($post['controllerList'], true);

		$this->assetGeneral = $this->nameGenerator($this->folderName);
		$this->layoutGeneral = strtolower($this->nameGenerator($this->folderName));

		foreach ($modelGenerateModelID as $key => $ModelID):

		    $this->general['ModelsCodeList'][$modelGenerateAction[$key]][$ModelID]['VariableName'] = $modelGenerateVariableName[$key];

		endforeach;

		for ($i = 0; $i < count($fileList); $i++):

		    $filename = pathinfo($fileList[$i]);

		    $HtmlFile = file_get_contents($folder . '/' . $fileList[$i]);

		    $genfilename = $this->nameGenerator($filename['filename']);

		    if ($this->validatesAsInt($genfilename[0])) {
			$genfilename = 'Html' . $genfilename;
		    }

		    $this->list[] = $genfilename;
		    $this->listOriginal[$genfilename] = $fileList[$i];

		    $this->urlReplace[$fileList[$i]] = '<?=Url::to(["/' . $folderName . '/' . strtolower($genfilename) . '"]); ?>';

//		    $this->general['ContentsList'][$genfilename]['source'] = $this->GetContent($HtmlFile, $genfilename);
//		    $this->general['ContentsList'][$genfilename]['file'] = $fileList[$i];

		    $this->assetsList[$genfilename]['fileName'] = $fileList[$i];
		    $this->assetsList[$genfilename]['asset'] = $this->getAssets($HtmlFile);

		    if (empty($this->layoutSourceFirst) && $fileName == $fileList[$i]) {
			$this->layoutSourceFirst = $HtmlFile;
		    }

		    $this->general['ActionsList'][$genfilename]['actionName'] = ucwords(strtolower($genfilename));
		    $this->general['ActionsList'][$genfilename]['fileName'] = $genfilename;
		    
		    unset($HtmlFile);

		endfor;

		foreach ($controllerList as $controllerListInfo):

		    $this->general['ControllersList'][$controllerListInfo['Name']] = $controllerListInfo;

		    foreach ($controllerListInfo['ActionList'] as $key => $controllerActionUrl):
			$this->urlReplace[$controllerListInfo['ActionFileName'][$key]] = '<?=Url::to(["/' . strtolower($controllerListInfo['Name']) . '/' . strtolower($controllerActionUrl) . '"]); ?>';
		    endforeach;

		endforeach;

		/*
		 * This code is generating ModelsCode into Controller's action.
		 */

		if (count($modelGenerateAction) > 0) :

		    foreach ($modelGenerateAction as $key => $actionName):

			$this->general['ActionModelList'][$actionName][$modelGenerateOriginalName[$key]][$modelGenerateVariableName[$key]] = $modelGenerateCode[$key];
			$this->general['ActionVariablesList'][$actionName] = $modelGenerateActionVariables[$key];

		    endforeach;

//		    $this->generateActionModel();

		endif;

//		foreach ($controllerName as $key => $controlName):
//
//		    $this->general['ControllersList'][$controlName] = $controllerAction[$key];
//
//		    foreach ($controllerAction[$key] as $controllerActionUrl):
//			$this->urlReplace[$this->listOriginal[$controllerActionUrl]] = '<?=Url::to(["/' . strtolower($controlName) . '/' . strtolower($controllerActionUrl) . '"]); ? >';
//		    endforeach;
//
//		endforeach;

		$this->layoutSource = $this->GenerateLayoutContent($this->layoutSourceFirst);

		$this->generateAssetList($folderName);
		$this->generateLayoutList($folderName);

		$this->generateAsset();
		$this->generateLayout();
		$this->generateContent();
		$this->generateController();

		$message = "Successful\r\n";
		$message .= "You need to put assets files into '<strong>assets/" . $folderName . "</strong>'\r\n";
		for ($i = 0; $i < count($controllerName); $i++):
		    $controllerlink = Url::to(['/' . strtolower($controllerName[$i])]);
		    $message .= "See: " . Html::a($controllerlink, $controllerlink, ['target' => '_blank']) . "\r\n";
		endfor;

		$results = "";
		foreach ($this->generatedFiles as $genFile):
		    $results .= $genFile['FileName'] . " Generated\r\n";
		    $results .= "For This:\r\n" . implode(" , ", $genFile['Files']) . "\r\n";
		    $results .= str_repeat("-", 30) . "\r\n\r\n";
		endforeach;

		return $this->renderFile(__DIR__ . "/views/results.php", ['results' => $results, 'message' => $message]);
	    }
	}

	$themeList = $this->dirToArray(Yii::getAlias('@app/template'));
	$themeList = ArrayHelper::map($themeList, 'theme', 'theme');

	return $this->renderFile(__DIR__ . "/views/client.php", ['themeList' => $themeList]);
    }

    public function actionSave($folder, $file, $modelname, $selector = null) {

	$post = Yii::$app->request->post();

	if (!empty($post['html'])) :

	    $htmlSave = [];

	    if (preg_match_all('@\<\?(.*?)\?\>@si', $post['html'], $phpGet)):

		foreach ($phpGet[1] as $phpSave):

		    $htmlSave['php']['{' . $modelname . uniqid() . '}'] = '<?' . $phpSave . '?>';

		endforeach;

		$htmlSave['HTML'] = strtr($post['html'], array_flip($htmlSave['php']));

	    else:

		$htmlSave['HTML'] = $post['html'];

	    endif;

	    $htmlSave['Selector'] = $selector;
	    $htmlSave['File'] = $file;
	    $htmlSave['Folder'] = $folder;

	    $FileCache = new FileCache();

	    $FileCache->set($modelname, json_encode($htmlSave));

	    return true;

	endif;
    }

    public function actionInclude($folder, $file, $contentSelector = null, $selector = null) {
	$folderName = $folder;
	$folder = $this->templatePath . $folderName;
	$HtmlFile = file_get_contents($folder . '/' . $file);

	include('includeFileExtra.php');

	$html = str_get_dom($HtmlFile);
	if (!is_null($selector)):


	    $selector = str_replace(':eq(0)', '', $selector);

	    try {
		return $html($selector, 0)->html();
	    } catch (Exception $e) {
		return 'Not Found';
	    }

	endif;


	foreach ($html('img') as $img):
	    $img->src = Yii::getAlias('@web/assets/' . $folderName . '/') . $img->src;
	endforeach;
	foreach ($html('link') as $link):
	    $link->href = Yii::getAlias('@web/assets/' . $folderName . '/') . $link->href;
	endforeach;

	foreach ($html('script') as $script):
	    $script->setOuterText('');
	endforeach;

//	dom_format($html, array('attributes_case' => CASE_LOWER));

	$resultHtml = $this->beautifyHtml(null, $html);

	$resultHtml = str_replace('</body>', $js, $resultHtml);

	return $resultHtml;
    }

    private function GetContent($HtmlFile, $genfilename) {

	if (!empty($this->contentSelector)) {

	    if (!(count($this->general['ModelsCodeList'][$genfilename]) > 0)) { // Code Optimizing...
		$html = SimpleHTMLDom::str_get_html($HtmlFile);

		if (!empty($this->headerSelector) && !empty($html->find($this->headerSelector, 0)->innertext)) {
		    $html->find($this->headerSelector, 0)->innertext = '';
		}

		if (!empty($this->footerSelector) && !empty($html->find($this->footerSelector, 0)->innertext)) {
		    $html->find($this->footerSelector, 0)->innertext = '';
		}

		foreach ($html->find('img') as $img):
		    $img->src = Yii::getAlias('@web/assets/' . $this->layoutGeneral . '/') . $img->src;
		endforeach;

		$contentJavascript = [];

		foreach ($html->find('head script') as $script):
		    if (!$script->src) {
			$js = str_replace('	', "\r\n", $script->innertext);
			$contentJavascriptIn['position'] = 'POS_HEAD';
			$contentJavascriptIn['js'] = \JSMinPlus::minify($js);
			$contentJavascript[] = $contentJavascriptIn;
			$script->outertext = '';
		    }
		endforeach;

		foreach ($html->find('body script') as $script):
		    if (!$script->src) {
			$js = str_replace('	', "\r\n", $script->innertext);
			$contentJavascriptIn['position'] = 'POS_END';
			$contentJavascriptIn['js'] = \JSMinPlus::minify($js);
			$contentJavascript[] = $contentJavascriptIn;
			$script->outertext = '';
		    }
		endforeach;

		$contentSource['javascript'] = $contentJavascript;
		$contentSource['source'] = $html->find($this->contentSelector, 0)->innertext;
		$html->clear();
		unset($html);

		$contentSource['source'] = $this->beautifyHtml($contentSource['source']);
	    } else {

		$html = str_get_dom($HtmlFile);

		if (!empty($this->headerSelector)) {
		    $html($this->headerSelector, 0)->setInnerText('');
		}

		if (!empty($this->footerSelector)) {
		    $html($this->footerSelector, 0)->setInnerText('');
		}

		foreach ($html('img') as $img):
		    $img->src = Yii::getAlias('@web/assets/' . $this->layoutGeneral . '/') . $img->src;
		endforeach;

		$contentJavascript = [];

		/*
		 * We're finding scripts code into "head" tag.
		 */
		
		foreach ($html('head script') as $script):
		    if (!$script->src) {
			$js = str_replace('	', "\r\n", $script->getInnerText());
			$contentJavascriptIn['position'] = 'POS_HEAD';
			$contentJavascriptIn['js'] = \JSMinPlus::minify($js);
			$contentJavascript[] = $contentJavascriptIn;
			$script->setOuterText('');
		    }
		endforeach;
		
		/*
		 * We're finding scripts code into "body" tag.
		 */

		foreach ($html('body script') as $script):
		    if (!$script->src) {
			$js = str_replace('	', "\r\n", $script->getInnerText());
			$contentJavascriptIn['position'] = 'POS_END';
			$contentJavascriptIn['js'] = \JSMinPlus::minify($js);
			$contentJavascript[] = $contentJavascriptIn;
			$script->setOuterText('');
		    }
		endforeach;

//	    $contentSelecterinLenght = 0;
//	    $contentSource = '';
//	    
//	    foreach($html->find($this->contentSelector) as $contentselect):
//		
//		$contentinside = $contentselect->innertext;
//		$contentlenght = mb_strlen($contentinside,'UTF-8');
//		
//		if($contentlenght > $contentSelecterinLenght){
//		    $contentSource = $contentinside;
//		    $contentSelecterinLenght = $contentlenght;
//		}
//		
//	    endforeach;
//	    $contentSource['source'] = $html->find($this->contentSelector, 0)->innertext;
//	    $html->clear();
//	    unset($html);

		$contentSource['javascript'] = $contentJavascript;

		$FileCache = new FileCache();

		$selectorList = [];
		
		

		foreach ($this->general['ModelsCodeList'][$genfilename] as $ModelsCodeID => $ModelsCodeInfo):

		    $modalCacheGet = json_decode($FileCache->get($ModelsCodeID), true);

		    if (!in_array($modalCacheGet['Selector'], $selectorList)) {
			$html($modalCacheGet['Selector'], 0)->setOuterText($modalCacheGet['HTML']);
			$selectorList[] = $modalCacheGet['Selector'];
		    }

		endforeach;


		$contentSource['source'] = $html($this->contentSelector, 0)->getInnerText();
		$contentSource['source'] = $this->beautifyHtml($contentSource['source']);

		foreach ($this->general['ModelsCodeList'][$genfilename] as $ModelsCodeID => $ModelsCodeInfo):

		    $modalCacheGet = json_decode($FileCache->get($ModelsCodeID), true);

		    $ModalVariableName = $this->general['ModelsCodeList'][$genfilename][$ModelsCodeID]['VariableName'];

		    if (count($modalCacheGet['php']) > 0):
			$changePhp = [];
			foreach ($modalCacheGet['php'] as $key => $php):
			    $changePhp[$key] = $php . "\r\n";
			endforeach;
			$contentSource['source'] = strtr($contentSource['source'], $changePhp);
		    endif;

		    $contentSource['source'] = str_replace('{MODELVARIABLENAME}', '$' . $ModalVariableName, $contentSource['source']);

		endforeach;
	    }

	    unset($html);

	    return $contentSource;
	}
	return false;
    }

    private function generateController() {

	foreach ($this->general['ControllersList'] as $controllerName => $controller):

	    $controllerActionListResult = '';
	    $controllerModelList = [];
	    $controllerModelListResult = '';

	    foreach ($controller['ActionList'] as $key => $actionListName):

		$ActionModelsGen = '';
		$ActionModelsVariablesGen = '';
		$actionName = $controllerName . '>' . $actionListName;
		$actionGenName = $controller['ActionFileGenName'][$key];

		if (count($this->general['ActionModelList'][$actionName]) > 0):

		    $ActionModelsGen .= "	// ActionModels\r\n\r\n";

		    foreach ($this->general['ActionModelList'][$actionName] as $ModelName => $ModelVariables):

			$controllerModelList[] = "use \\app\\models\\" . $ModelName . ";";

			foreach ($ModelVariables as $ModelVariableName => $ModelCode):

			    $ActionModelsGen .= '	$' . $ModelVariableName . ' = ' . $ModelName . '::' . $ModelCode . ";\r\n";
			    $ActionModelsVariablesGen .= "'" . $ModelVariableName . "' => $" . $ModelVariableName . ",";

			endforeach;
		    endforeach;

		    $ActionModelsGen .= "\r\n	// ActionModels\r\n";

		endif;

		$controllerActionList = $this->actionTemplate . "\r\n";
		$controllerActionList = str_replace('{' . self::ACTIONNAME . '}', $actionListName, $controllerActionList);
		$controllerActionList = str_replace('{' . self::ACTIONFILENAME . '}', $actionListName, $controllerActionList);
		$controllerActionList = str_replace('{' . self::ACTIONLAYOUT . '}', $this->general['ActionsList'][$actionGenName]['layout'], $controllerActionList);
		$controllerActionList = str_replace('{' . self::ACTIONMODELS . '}', $ActionModelsGen, $controllerActionList);
		$controllerActionList = str_replace('{' . self::ACTIONMODELSVARIABLES . '}', $ActionModelsVariablesGen, $controllerActionList);
		$controllerActionList = str_replace('{' . self::ACTIONVARIABLES . '}', $this->general['ActionVariablesList'][$actionGenName], $controllerActionList);
		$controllerActionListResult .= $controllerActionList;

	    endforeach;

	    if (count($controllerModelList) > 0) {
		$controllerModelList = array_unique($controllerModelList);
		$controllerModelListResult = implode("\r\n", $controllerModelList);
	    }

	    $fileSaveName = Yii::getAlias('@app/controllers/' . $controllerName . 'Controller.php');

	    $ControllerTemplate = $this->TemplateOpen($this->controllerTemplate);
	    $ControllerTemplate = $this->changeAsset($ControllerTemplate, $controllerName, self::CONTROLLERNAME);
	    $ControllerTemplate = $this->changeAsset($ControllerTemplate, $controllerActionListResult, self::CONTROLLERACTIONLIST);
	    $ControllerTemplate = $this->changeAsset($ControllerTemplate, $controllerModelListResult, self::CONTROLLERMODELS);
	    $ControllerTemplate = $this->changeAsset($ControllerTemplate, $this->appname, self::APPNAME);

	    $fileArray['FileName'] = $fileSaveName;
	    $fileArray['Files'] = [$controllerName . 'Controller.php'];
	    $this->generatedFiles[] = $fileArray;
	    $this->save($fileSaveName, $ControllerTemplate);

	endforeach;
    }

    private function beautifyHtml($source = null, $html = null) {
	if (is_null($html)) {
	    $html = str_get_dom($source);
	}
	ob_start();
	dom_format($html, array('attributes_case' => CASE_LOWER));
	echo $html;
	$source = ob_get_contents();
	ob_end_clean();
	return $source;
    }

    private function GenerateLayoutContent($HtmlFile) {

	$html = SimpleHTMLDom::str_get_html($HtmlFile);

	foreach ($html->find('img') as $img):
	    $img->src = Yii::getAlias('@web/assets/' . $this->layoutGeneral . '/') . $img->src;
	endforeach;

	$includeFileOver = '<?php
use yii\helpers\Url;
?>';

	if (!empty($this->headerSelector) && !empty($html->find($this->headerSelector, 0)->innertext)) {
	    $headerSource = $html->find($this->headerSelector, 0)->innertext;
	    $html->find($this->headerSelector, 0)->innertext = '{HEADERINCLUDE}';
	    $fileSaveName = Yii::getAlias('@app/views/layouts/' . $this->layoutGeneral . '_header.php');
//	    $headerSource = \serhatozles\htmlawed\htmLawed::htmLawed($headerSource, array('tidy'=>'1t1')); 
	    $headerSource = $this->beautifyHtml($headerSource);
	    $headerSource = strtr($headerSource, $this->urlReplace);
	    $headerSource = $includeFileOver . $headerSource;

	    $this->save($fileSaveName, $headerSource);
	}

	if (!empty($this->contentSelector) && !empty($html->find($this->contentSelector, 0)->innertext)) {
//	    $contentSource = $html->find($this->contentSelector, 0)->innertext;
	    $html->find($this->contentSelector, 0)->innertext = '{CONTENT}';
	}

	if (!empty($this->footerSelector) && !empty($html->find($this->footerSelector, 0)->innertext)) {
	    $footerSource = $html->find($this->footerSelector, 0)->innertext;
	    $html->find($this->footerSelector, 0)->innertext = '{INCLUDEFOOTER}';
	    $footerSource = $this->beautifyHtml($footerSource);
	    $fileSaveName = Yii::getAlias('@app/views/layouts/' . $this->layoutGeneral . '_footer.php');
	    $footerSource = strtr($footerSource, $this->urlReplace);
	    $footerSource = $includeFileOver . $footerSource;

	    $this->save($fileSaveName, $footerSource);
	}


	$html->set_callback('serhatozles\themeintegrator\Controller::StyleRemover');

	$html->find('html', 0)->lang = '{HTMLLANG}';
	$html->find('title', 0)->innertext = '{HTMLTITLE}';
	$html->find('head', 0)->innertext .= '{CSRFMETA}';
	$html->find('body', 0)->innertext = '{BEGINBODY}' . $html->find('body', 0)->innertext;
	$html->find('body', 0)->innertext .= '{ENDBODY}';

	$htmlresult = $html->save();
	$htmlresult = $this->beautifyHtml($htmlresult);
	$htmlresult = str_replace('{HEADERINCLUDE}', '<?php echo $this->render("' . $this->layoutGeneral . '_header"); ?>', $htmlresult);
	$htmlresult = str_replace('{CONTENT}', '<?php echo $content ?>', $htmlresult);
	$htmlresult = str_replace('{INCLUDEFOOTER}', '<?php echo $this->render("' . $this->layoutGeneral . '_footer"); ?>', $htmlresult);
	$htmlresult = str_replace('{HTMLLANG}', '<?php echo Yii::$app->language ?>', $htmlresult);
	$htmlresult = str_replace('{HTMLTITLE}', '<?= Html::encode($this->title) ?>', $htmlresult);
	$htmlresult = str_replace('{CSRFMETA}', '<?php echo Html::csrfMetaTags() ?><?php $this->head() ?>', $htmlresult);
	$htmlresult = str_replace('{BEGINBODY}', '<?php $this->beginBody() ?>', $htmlresult);
	$htmlresult = str_replace('{ENDBODY}', '<?php $this->endBody() ?>', $htmlresult);
	$htmlresult = '<?php
use yii\helpers\Html;
{LAYOUTASSETSUSE}

/**
 * extension: HTML Template Integrator
 */

/* @var $this \yii\web\View */
/* @var $content string */
/* files: {LAYOUTFILESLIST} */

{LAYOUTASSETSREGISTER}
?>
<?php $this->beginPage() ?>
' . $htmlresult;

	$htmlresult .= '<?php $this->endPage() ?>';

	$html->clear();
	unset($html);

	return $htmlresult;
    }

    public static function StyleRemover($element) {

	if ($element->tag == 'link') {
	    $element->outertext = '';
	}

//	if ($element->tag == 'script' && !empty($element->src)) {
	if ($element->tag == 'script') {
	    $element->outertext = '';
	}
    }

    private function generateLayout() {

	foreach ($this->general['LayoutsList'] as $layoutName => $layout):

	    $AssetsUse = implode("\r\n", array_map(function ($str) {
				return 'use ' . $this->appname . '\assets\\' . $str . 'Asset;';
			    }, $layout['assets'])) . "\r\n";
	    $AssetsRegister = implode("\r\n", array_map(function ($str) {
				return $str . 'Asset::register($this);';
			    }, $layout['assets'])) . "\r\n";

//	    $LayoutTemplate = $this->TemplateOpen($this->layoutTemplate);
	    $LayoutTemplate = $this->layoutSource;
	    $LayoutTemplate = $this->changeAsset($LayoutTemplate, $AssetsUse, self::LAYOUTASSETSUSE);
	    $LayoutTemplate = $this->changeAsset($LayoutTemplate, $AssetsRegister, self::LAYOUTASSETSREGISTER);
	    $LayoutTemplate = $this->changeAsset($LayoutTemplate, implode(',', $layout['filesOriginal']), self::LAYOUTFILESLIST);

	    $fileSaveName = Yii::getAlias('@app/views/layouts/' . $layoutName . '.php');
	    $fileArray['FileName'] = $fileSaveName;
	    $fileArray['Files'] = $layout['filesOriginal'];
	    $this->generatedFiles[] = $fileArray;
	    $this->save($fileSaveName, $LayoutTemplate);

	endforeach;
    }

    private function generateContent() {

	foreach ($this->general['ControllersList'] as $controllerName => $controller):

	    $fileSaveName = Yii::getAlias('@app/views/' . strtolower($controllerName) . '/');
	    $this->folderCreate($fileSaveName);

	    foreach ($controller['ActionList'] as $key => $actionListName):

		$actionGenName = $controller['ActionFileGenName'][$key];
		$actionOrginalName = $controller['ActionFileName'][$key];
		$actionName = $controllerName . '>' . $actionListName;
		
		$fileSaveName = Yii::getAlias('@app/views/' . strtolower($controllerName) . '/');
		
		$HtmlFile = file_get_contents($this->templatePath . $this->folderName . '/' . $actionOrginalName);

//		$content = $this->general['ContentsList'][$actionGenName];
		$content = $this->GetContent($HtmlFile, $actionName);

		$content['source'] = strtr($content['source'], $this->urlReplace);

		$OverContent = '<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
$this->title = "' . $actionListName . '";
    ';
		foreach ($content['javascript'] as $javascript):
		    $javascriptInside = "\$this->registerJs(\"" . addslashes($javascript['js']) . "\",\yii\web\View::" . $javascript['position'] . ");";
		    $OverContent .= $javascriptInside;
		endforeach;
		$OverContent .= '
?>
';

		$content['source'] = $OverContent . $content['source'];

		$fileSaveName .= $actionListName . '.php';
		$fileArray['FileName'] = $fileSaveName;
		$fileArray['Files'] = [$actionOrginalName];
		$this->generatedFiles[] = $fileArray;
		$this->save($fileSaveName, $content['source']);

	    endforeach;

	endforeach;
    }

    private function generateLayoutList($folderName) {

	$GenExtra = 0;

	$newLayouts = [];
	$newLayoutsList = [];

	foreach ($this->layoutsFirstList as $layoutName => $layout):
	    foreach ($layout['filesOriginal'] as $key => $layoutFile):
		$newLayouts[$layoutFile]['assets'][] = $layoutName;
		$newLayouts[$layoutFile]['files'] = $layout['files'][$key];
	    endforeach;
	endforeach;

	foreach ($newLayouts as $file => $layout):
	    $newLayoutsList[serialize($layout['assets'])]['layoutFile'][$layout['files']] = $file;
	    $newLayoutsList[serialize($layout['assets'])]['assets'] = $layout['assets'];
	endforeach;

	foreach ($newLayoutsList as $layout):
	    $GenExtraTo = $GenExtra != 0 ? $this->nameGenerator(Enum::numToWords($GenExtra)) : '';
	    $layoutName = $this->layoutGeneral . $GenExtraTo;

	    $this->general['LayoutsList'][$layoutName]['foldername'] = $folderName;
	    $this->general['LayoutsList'][$layoutName]['filesOriginal'] = $layout['layoutFile'];
	    $this->general['LayoutsList'][$layoutName]['assets'] = $layout['assets'];

	    foreach ($layout['layoutFile'] as $key => $value):

		$this->general['ActionsList'][$key]['layout'] = $layoutName;

	    endforeach;

	    $GenExtra++;
	endforeach;
    }

    private function generateAssetList($folderName) {

	$GenExtra = 0;

	$newAssets = [];
	$newAssetsList = [];

	foreach ($this->assetsList as $assetName => $asset):
	    foreach ($asset['asset'] as $assetFile):
		$newAssets[$assetFile]['files'][] = $assetName;
		$newAssets[$assetFile]['filesOriginal'][] = $asset['fileName'];
	    endforeach;
	endforeach;

	foreach ($newAssets as $file => $asset):
	    $newAssetsList[serialize($asset['files'])]['assetFile'][] = $file;
	    $newAssetsList[serialize($asset['files'])]['files'] = $asset['files'];
	    $newAssetsList[serialize($asset['files'])]['filesOriginal'] = $asset['filesOriginal'];
	endforeach;

	foreach ($newAssetsList as $asset):
	    $GenExtraTo = $GenExtra != 0 ? $this->nameGenerator(Enum::numToWords($GenExtra)) : '';

	    $justCss = $this->getFileType($asset['assetFile'], 'css');
	    $justJs = $this->getFileType($asset['assetFile'], 'js');

	    $assetName = $this->assetGeneral . $GenExtraTo;

	    $this->general['AssetsList'][$assetName]['files'] = implode(',', $asset['files']);
	    $this->general['AssetsList'][$assetName]['filesOriginal'] = $asset['filesOriginal'];
	    $this->general['AssetsList'][$assetName]['foldername'] = $folderName;
	    $this->general['AssetsList'][$assetName]['css'] = $justCss;
	    $this->general['AssetsList'][$assetName]['js'] = $justJs;

	    $this->layoutsFirstList[$assetName]['filesOriginal'] = $asset['filesOriginal'];
	    $this->layoutsFirstList[$assetName]['files'] = $asset['files'];

	    $GenExtra++;
	endforeach;
    }

    private function generateAsset() {

	foreach ($this->general['AssetsList'] as $assetName => $asset):
	    $rsjustCss = "\r\n" . implode(',' . "\r\n", array_map(function ($str) {
				return "'" . $str . "'";
			    }, $asset['css'])) . "\r\n";
	    $rsjustJs = "\r\n" . implode(',' . "\r\n", array_map(function ($str) {
				return "'" . $str . "'";
			    }, $asset['js'])) . "\r\n";

	    $AssetTemplate = $this->TemplateOpen($this->assetTemplate);
	    $AssetTemplate = $this->changeAsset($AssetTemplate, $assetName . 'Asset', self::ASSETNAME);
	    $AssetTemplate = $this->changeAsset($AssetTemplate, $rsjustCss, self::ASSETCSSLIST);
	    $AssetTemplate = $this->changeAsset($AssetTemplate, $rsjustJs, self::ASSETJSLIST);
	    $AssetTemplate = $this->changeAsset($AssetTemplate, 'assets/' . $asset['foldername'], self::ASSETFOLDER);
	    $AssetTemplate = $this->changeAsset($AssetTemplate, $this->appname, self::APPNAME);
	    $AssetTemplate = $this->changeAsset($AssetTemplate, implode(',', $asset['filesOriginal']), self::ASSETFILESLIST);


	    $fileSaveName = Yii::getAlias('@app/assets/' . $assetName . 'Asset.php');
	    $fileArray['FileName'] = $fileSaveName;
	    $fileArray['Files'] = $asset['filesOriginal'];
	    $this->generatedFiles[] = $fileArray;
	    $this->save($fileSaveName, $AssetTemplate);
	endforeach;
    }

    function changeAsset($Template, $Change, $To) {

	return str_replace('{' . $To . '}', $Change, $Template);
    }

    function TemplateOpen($Template) {

	return file_get_contents(__DIR__ . $Template);
    }

    function getAssets($Source) {

	$html = SimpleHTMLDom::str_get_html($Source);
	$Links = [];

	foreach ($html->find('link[rel=stylesheet]') as $link) {

	    $Links[] = $link->href;
	}

	foreach ($html->find('script') as $link) {

	    $Links[] = $link->src;
	}

	$html->clear();
	unset($html);

	return array_values(array_filter($Links));
    }

    function nameGenerator($baslik) {
	$bul = array('Ç', 'Ş', 'Ğ', 'Ü', 'İ', 'Ö', 'ç', 'ş', 'ğ', 'ü', 'ö', 'ı', '-', '_');
	$yap = array('c', 's', 'g', 'u', 'i', 'o', 'c', 's', 'g', 'u', 'o', 'i', ' ', ' ');
	$perma = str_replace($bul, $yap, $baslik);
	$perma = preg_replace("@[^A-Za-z0-9\-_]@i", ' ', $perma);
	$perma = trim(preg_replace('/\s+/', ' ', $perma));
	$perma = ucwords(strtolower($perma));
	$perma = str_replace(' ', '', $perma);
	return $perma;
    }

    function getHtml($dir) {
	$thelist = [];
	if ($handle = opendir($dir)) {
	    while (false !== ($file = readdir($handle))) {
		if ($file != "." && $file != ".." && strtolower(substr($file, strrpos($file, '.') + 1)) == 'html') {
		    $thelist[] = $file;
		}
	    }
	    closedir($handle);
	}
	return $thelist;
    }

    function getModels() {
	$thelist = [];
	$dir = Yii::getAlias('@app/models/');
	if ($handle = opendir($dir)) {
	    while (false !== ($file = readdir($handle))) {
		if ($file != "." && $file != ".." && strtolower(substr($file, strrpos($file, '.') + 1)) == 'php') {
		    $file_info = pathinfo($file);
		    $ModelInside = file_get_contents($dir . $file);

		    if (preg_match('@attributeLabels[^>]+return(.*?)\}@si', $ModelInside, $ModelAttr)):
			$ModelAttr[1] = preg_replace("@Yii::t\('.*?'.*?'(.*?)'\)@si",'$1',$ModelAttr[1]);	
			eval('$ModelAttr = ' . $ModelAttr[1]);
			$thelistinside['ModelName'] = $file_info['filename'];
			$thelistinside['ModelAttr'] = $ModelAttr;
			$thelist[] = $thelistinside;
			unset($ModelInside);
		    endif;
		}
	    }
	    closedir($handle);
	}
	return $thelist;
    }

    function getFileType($array, $type) {

	$result = [];

	foreach ($array as $file):

	    if (strtolower(substr($file, strrpos($file, '.') + 1)) == $type) {
		$result[] = $file;
	    }

	endforeach;

	return $result;
    }

    private function dirToArray($dir) {

	$result = array();

	$cdir = scandir($dir);
	foreach ($cdir as $key => $value) {
	    if (!in_array($value, array(".", ".."))) {
		if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
		    $result[]['theme'] = $value;
		}
	    }
	}

	return $result;
    }

    function validatesAsInt($number) {
	$number = filter_var($number, FILTER_VALIDATE_INT);
	return ($number !== FALSE);
    }

    private function save($fileSaveName, $AssetTemplate) {
	return file_put_contents($fileSaveName, $AssetTemplate);
    }

    private function folderCreate($folderName) {
	if (!is_dir($folderName)) {
	    return FileHelper::createDirectory($folderName);
	}
    }

}
