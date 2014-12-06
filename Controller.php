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

include(__DIR__ . '/ganon/ganon.php');
include(__DIR__ . '/ganon/third party/jsminplus.php');

set_time_limit(0);

class Controller extends BaseController {

    public $generalAssetsList = [];
    public $generalLayoutsList = [];
    public $generalContentsList = [];
    public $generalActionsList = [];
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
    const CONTROLLERACTIONLIST = "CONTROLLERACTIONLIST";

    public $actionTemplate = '
    /**
    * @actionName: {ACTIONNAME}
    * @layout: {ACTIONLAYOUT}
    */
    public function action{ACTIONNAME}() {
	$this->layout = "{ACTIONLAYOUT}";
	return $this->render("{ACTIONFILENAME}");
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

	if (Yii::$app->request->isPost) {

	    $post = Yii::$app->request->post();

	    $folderName = $post['folder'];
	    if ($post['step'] == 1) {
		$folder = $this->templatePath . $folderName;
		$fileList = $this->getHtml($folder);

		$fileList = array_combine($fileList, $fileList);

		return $this->renderFile(__DIR__ . "/views/file.php", ['fileList' => $fileList, 'folder' => $folderName]);
	    } elseif ($post['step'] == 2) {

		$fileName = $post['file'];
		$this->folderName = $folderName;
		$this->headerSelector = $post['headerselector'];
		$this->contentSelector = $post['contentselector'];
		$this->footerSelector = $post['footerselector'];

		$this->assetGeneral = $this->nameGenerator($this->folderName);
		$this->layoutGeneral = strtolower($this->nameGenerator($this->folderName));

		$folder = $this->templatePath . $post['folder'];
		$fileList = $this->getHtml($folder);

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

		    $this->generalContentsList[$genfilename]['source'] = $this->GetContent($HtmlFile);
		    $this->generalContentsList[$genfilename]['file'] = $fileList[$i];

		    $this->assetsList[$genfilename]['fileName'] = $fileList[$i];
		    $this->assetsList[$genfilename]['asset'] = $this->getAssets($HtmlFile);

		    if (empty($this->layoutSourceFirst) && $fileName == $fileList[$i]) {
			$this->layoutSourceFirst = $HtmlFile;
		    }

		    $this->generalActionsList[$genfilename]['actionName'] = ucwords(strtolower($genfilename));
		    $this->generalActionsList[$genfilename]['fileName'] = $genfilename;

		endfor;

		$this->layoutSource = $this->GenerateLayoutContent($this->layoutSourceFirst);

		$this->generateAssetList($folderName);
		$this->generateLayoutList($folderName);

		$this->generateAsset();
		$this->generateLayout();
		$this->generateContent();
		$this->generateController();

		$message = "Successful\r\n";
		$message .= "You need to put assets files into '<strong>assets/" . $folderName . "</strong>'\r\n";
		$controllerlink = Url::to(['/' . $folderName]);
		$message .= "See: " . Html::a($controllerlink, $controllerlink, ['target' => '_blank']) . "\r\n";

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

    private function generateController() {

	$controllerActionListResult = '';

	foreach ($this->generalActionsList as $actionName => $action):

	    $controllerActionList = $this->actionTemplate . "\r\n";
	    $controllerActionList = str_replace('{' . self::ACTIONNAME . '}', $action['actionName'], $controllerActionList);
	    $controllerActionList = str_replace('{' . self::ACTIONFILENAME . '}', $action['fileName'], $controllerActionList);
	    $controllerActionList = str_replace('{' . self::ACTIONLAYOUT . '}', $action['layout'], $controllerActionList);
	    $controllerActionListResult .= $controllerActionList;

	endforeach;

	$fileSaveName = Yii::getAlias('@app/controllers/' . $this->assetGeneral . 'Controller.php');

	$ControllerTemplate = $this->TemplateOpen($this->controllerTemplate);
	$ControllerTemplate = $this->changeAsset($ControllerTemplate, $this->assetGeneral, self::CONTROLLERNAME);
	$ControllerTemplate = $this->changeAsset($ControllerTemplate, $controllerActionListResult, self::CONTROLLERACTIONLIST);
	$ControllerTemplate = $this->changeAsset($ControllerTemplate, $this->appname, self::APPNAME);

	$fileArray['FileName'] = $fileSaveName;
	$fileArray['Files'] = [$this->assetGeneral . 'Controller.php'];
	$this->generatedFiles[] = $fileArray;
	$this->save($fileSaveName, $ControllerTemplate);
    }

    private function GetContent($HtmlFile) {

	if (!empty($this->contentSelector)) {
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

	    foreach ($html->find('script') as $script):
		if (!$script->src) {
		    $contentJavascript[] = \JSMinPlus::minify($script->innertext);
		    $script->outertext = '';
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



	    $contentSource['source'] = $html->find($this->contentSelector, 0)->innertext;

	    $html->clear();
	    unset($html);

	    $contentSource['javascript'] = $contentJavascript;
	    
	    $contentSource['source'] = $this->beautifyHtml($contentSource['source']);


	    return $contentSource;
	}
	return false;
    }

    private function beautifyHtml($source) {
	$html = str_get_dom($source);
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

	if (!empty($this->headerSelector)) {
	    $headerSource = $html->find($this->headerSelector, 0)->innertext;
	    $html->find($this->headerSelector, 0)->innertext = '{HEADERINCLUDE}';
	    $fileSaveName = Yii::getAlias('@app/views/layouts/' . $this->layoutGeneral . '_header.php');
//	    $headerSource = \serhatozles\htmlawed\htmLawed::htmLawed($headerSource, array('tidy'=>'1t1')); 
	    $headerSource = $this->beautifyHtml($headerSource);
	    $headerSource = strtr($headerSource, $this->urlReplace);
	    $headerSource = $includeFileOver . $headerSource;

	    $this->save($fileSaveName, $headerSource);
	}

	if (!empty($this->contentSelector)) {
//	    $contentSource = $html->find($this->contentSelector, 0)->innertext;
	    $html->find($this->contentSelector, 0)->innertext = '{CONTENT}';
	}

	if (!empty($this->footerSelector)) {
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
	$htmlresult = str_replace('{HEADERINCLUDE}','<?php echo $this->render("' . $this->layoutGeneral . '_header"); ?>',$htmlresult);
	$htmlresult = str_replace('{CONTENT}','<?php echo $content ?>',$htmlresult);
	$htmlresult = str_replace('{INCLUDEFOOTER}','<?php echo $this->render("' . $this->layoutGeneral . '_footer"); ?>',$htmlresult);
	$htmlresult = str_replace('{HTMLLANG}','<?php echo Yii::$app->language ?>',$htmlresult);
	$htmlresult = str_replace('{HTMLTITLE}','<?= Html::encode($this->title) ?>',$htmlresult);
	$htmlresult = str_replace('{CSRFMETA}','<?php echo Html::csrfMetaTags() ?><?php $this->head() ?>',$htmlresult);
	$htmlresult = str_replace('{BEGINBODY}','<?php $this->beginBody() ?>',$htmlresult);
	$htmlresult = str_replace('{ENDBODY}','<?php $this->endBody() ?>',$htmlresult);
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

	foreach ($this->generalLayoutsList as $layoutName => $layout):

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

	foreach ($this->generalContentsList as $contentName => $content):

	    $fileSaveName = Yii::getAlias('@app/views/' . $this->layoutGeneral . '/');
	    $this->folderCreate($fileSaveName);

	    $content['source']['source'] = strtr($content['source']['source'], $this->urlReplace);

	    $OverContent = '<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
$this->title = "' . $contentName . '";
    ';
	    foreach ($content['source']['javascript'] as $javascript):
		$javascriptInside = '$this->registerJs("' . addslashes($javascript) . '",\yii\web\View::POS_END);';
		$OverContent .= $javascriptInside;
	    endforeach;
	    $OverContent .= '
?>
';

	    $content['source']['source'] = $OverContent . $content['source']['source'];

	    $fileSaveName .= $contentName . '.php';
	    $fileArray['FileName'] = $fileSaveName;
	    $fileArray['Files'] = [$content['file']];
	    $this->generatedFiles[] = $fileArray;
	    $this->save($fileSaveName, $content['source']['source']);

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

	    $this->generalLayoutsList[$layoutName]['foldername'] = $folderName;
	    $this->generalLayoutsList[$layoutName]['filesOriginal'] = $layout['layoutFile'];
	    $this->generalLayoutsList[$layoutName]['assets'] = $layout['assets'];

	    foreach ($layout['layoutFile'] as $key => $value):

		$this->generalActionsList[$key]['layout'] = $layoutName;

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

	    $this->generalAssetsList[$assetName]['files'] = implode(',', $asset['files']);
	    $this->generalAssetsList[$assetName]['filesOriginal'] = $asset['filesOriginal'];
	    $this->generalAssetsList[$assetName]['foldername'] = $folderName;
	    $this->generalAssetsList[$assetName]['css'] = $justCss;
	    $this->generalAssetsList[$assetName]['js'] = $justJs;

	    $this->layoutsFirstList[$assetName]['filesOriginal'] = $asset['filesOriginal'];
	    $this->layoutsFirstList[$assetName]['files'] = $asset['files'];

	    $GenExtra++;
	endforeach;
    }

    private function generateAsset() {

	foreach ($this->generalAssetsList as $assetName => $asset):
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
	$bul = array('Ç', 'Ş', 'Ğ', 'Ü', 'İ', 'Ö', 'ç', 'ş', 'ğ', 'ü', 'ö', 'ı', '-');
	$yap = array('c', 's', 'g', 'u', 'i', 'o', 'c', 's', 'g', 'u', 'o', 'i', ' ');
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
