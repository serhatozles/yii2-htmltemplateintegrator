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
use kartik\helpers\Enum;
use yii\web\Controller as BaseController;

set_time_limit(0);

class Controller extends BaseController {

    public $generalAssetsList = [];
    public $generalLayoutsList = [];
    public $generalContentsList = [];
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
    public function action{ACTIONNAME}() {
	return $this->render("{ACTIONFILENAME}");
    }';
    public $headerSelector = '';
    public $contentSelector = '';
    public $footerSelector = '';
    public $layoutSource = '';
    public $controllerActionList = '';
    public $folderName = null;

    function init() {
	$this->appname = str_replace('app-', '', Yii::$app->id);
	$this->templatePath = Yii::getAlias('@app/template/');
	$this->folderCreate($this->templatePath);
    }

    public function actionDefine() {

	if (Yii::$app->request->isPost) {

	    $post = Yii::$app->request->post();

	    $folderName = $post['folder'];
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
		$genfilename = $this->nameGenerator($filename['filename']);

		if ($this->validatesAsInt($genfilename[0])) {
		    $genfilename = 'Html' . $genfilename;
		}

		$this->list[] = $genfilename;
		$this->listOriginal[$genfilename] = $fileList[$i];

		$HtmlFile = file_get_contents($folder . '/' . $fileList[$i]);

		$this->generalContentsList[$genfilename]['source'] = $this->GetContent($HtmlFile);
		$this->generalContentsList[$genfilename]['file'] = $fileList[$i];

		$this->assetsList[$genfilename] = $this->getAssets($HtmlFile);

		if (empty($this->layoutSource)) {
		    $this->layoutSource = $this->GenerateLayoutContent($HtmlFile);
		}

		$controllerActionList = $this->actionTemplate . "\r\n";
		$controllerActionList = str_replace('{' . self::ACTIONNAME . '}', $genfilename, $controllerActionList);
		$controllerActionList = str_replace('{' . self::ACTIONFILENAME . '}', $genfilename, $controllerActionList);
		$this->controllerActionList .= $controllerActionList;

	    endfor;

	    $this->generateAssetList($folderName);
	    $this->generateLayoutList($folderName);

	    $this->generateAsset();
	    $this->generateLayout();
	    $this->generateContent();
	    $this->generateController();

	    $message = "Successful\r\n";
	    $message .= "You need to put assets files into '<strong>assets/" . $folderName . "</strong>'\r\n";

	    $results = "";
	    foreach ($this->generatedFiles as $genFile):
		$results .= $genFile['FileName'] . " Generated\r\n";
		$results .= "For This:\r\n" . implode(" , ", $genFile['Files']) . "\r\n";
		$results .= str_repeat("-", 30) . "\r\n\r\n";
	    endforeach;

	    return $this->renderFile(__DIR__ . "/views/results.php", ['results' => $results, 'message' => $message]);
	}

	$themeList = $this->dirToArray(Yii::getAlias('@app/template'));
	$themeList = ArrayHelper::map($themeList, 'theme', 'theme');

	return $this->renderFile(__DIR__ . "/views/client.php", ['themeList' => $themeList]);
    }

    private function generateController() {

	$fileSaveName = Yii::getAlias('@app/controllers/' . $this->assetGeneral . 'Controller.php');

	$controllerActionList = $this->controllerActionList;
	$ControllerTemplate = $this->TemplateOpen($this->controllerTemplate);
	$ControllerTemplate = $this->changeAsset($ControllerTemplate, $this->assetGeneral, self::CONTROLLERNAME);
	$ControllerTemplate = $this->changeAsset($ControllerTemplate, $controllerActionList, self::CONTROLLERACTIONLIST);
	$ControllerTemplate = $this->changeAsset($ControllerTemplate, $this->appname, self::APPNAME);

	$fileArray['FileName'] = $fileSaveName;
	$fileArray['Files'] = [$this->assetGeneral . 'Controller.php'];
	$this->generatedFiles[] = $fileArray;
	$this->save($fileSaveName, $ControllerTemplate);
    }

    private function GetContent($HtmlFile) {

	if (!empty($this->contentSelector)) {
	    $html = SimpleHTMLDom::str_get_html($HtmlFile);

	    $contentSource = $html->find($this->contentSelector, 0)->innertext;

//	    $contentSource = \Mihaeu\HtmlFormatter::format($contentSource);

	    $html->clear();
	    unset($html);

	    return $contentSource;
	}
	return false;
    }

    private function GenerateLayoutContent($HtmlFile) {

	$html = SimpleHTMLDom::str_get_html($HtmlFile);

	if (!empty($this->headerSelector)) {
	    $headerSource = $html->find($this->headerSelector, 0)->innertext;
	    $html->find($this->headerSelector, 0)->innertext = '<?php include("' . $this->layoutGeneral . '_header.php"); ?>';
	    $fileSaveName = Yii::getAlias('@app/views/layouts/' . $this->layoutGeneral . '_header.php');
	    $this->save($fileSaveName, $headerSource);
	}

	if (!empty($this->contentSelector)) {
//	    $contentSource = $html->find($this->contentSelector, 0)->innertext;
	    $html->find($this->contentSelector, 0)->innertext = '<?php echo $content ?>';
	}

	if (!empty($this->footerSelector)) {
	    $footerSource = $html->find($this->footerSelector, 0)->innertext;
	    $html->find($this->footerSelector, 0)->innertext = '<?php include("' . $this->layoutGeneral . '_footer.php"); ?>';
	    $fileSaveName = Yii::getAlias('@app/views/layouts/' . $this->layoutGeneral . '_footer.php');
	    $this->save($fileSaveName, $footerSource);
	}

	$html->set_callback('serhatozles\themeintegrator\Controller::StyleRemover');

	$html->find('html', 0)->lang = '<?php echo Yii::$app->language ?>';
	$html->find('title', 0)->innertext = '<?= Html::encode($this->title) ?>';
	$html->find('head', 0)->innertext .= '<?php echo Html::csrfMetaTags() ?><?php $this->head() ?>';
	$html->find('body', 0)->innertext = '<?php $this->beginBody() ?>' . $html->find('body', 0)->innertext;
	$html->find('body', 0)->innertext .= '<?php $this->endBody() ?>';

	$htmlresult = $html->save();

	$htmlresult = \Mihaeu\HtmlFormatter::format($htmlresult);

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

    private function generateLayoutList($folderName) {

	$GenExtra = 0;

	while (count($this->layoutsFirstList) > 0):

	    $GenExtraTo = $GenExtra != 0 ? $this->nameGenerator(Enum::numToWords($GenExtra)) : '';

	    $layoutName = $this->layoutGeneral . $GenExtraTo;

	    extract($this->layoutsFirstList);

	    if (count($this->layoutsFirstList) > 1):
		$run = '$intersectresult = array_intersect($' . implode(',$', $this->layoutsList) . ');';
		eval($run);
	    else:
		$intersectresult = $this->layoutsFirstList[$this->layoutsList[0]];
	    endif;

	    $this->generalLayoutsList[$layoutName]['foldername'] = $folderName;
	    $this->generalLayoutsList[$layoutName]['filesOriginal'] = $intersectresult;
	    $this->generalLayoutsList[$layoutName]['assets'] = $this->layoutsList;

	    $this->layoutClear($intersectresult);

	    $GenExtra++;

	endwhile;
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

	    $fileSaveName .= $contentName . '.php';
	    $fileArray['FileName'] = $fileSaveName;
	    $fileArray['Files'] = [$content['file']];
	    $this->generatedFiles[] = $fileArray;
	    $this->save($fileSaveName, $content['source']);

	endforeach;
    }

    private function layoutClear($clearList) {

	foreach ($this->layoutsFirstList as $layoutKey => $layout):

	    $diff = array_diff($layout, $clearList);

	    if (count($diff) > 0) {
		$this->layoutsFirstList[$layoutKey] = $diff;
	    } else {
		unset($this->layoutsFirstList[$layoutKey]);
		unset($this->layoutsList[array_search($layoutKey, $this->layoutsList)]);
	    }

	endforeach;
    }

    private function assetClear($clearList) {

	foreach ($this->assetsList as $assetKey => $asset):

	    $diff = array_diff($asset, $clearList);

	    if (count($diff) > 0) {
		$this->assetsList[$assetKey] = $diff;
	    } else {
		unset($this->assetsList[$assetKey]);
		unset($this->list[array_search($assetKey, $this->list)]);
		unset($this->listOriginal[$assetKey]);
	    }

	endforeach;
    }

    private function generateAssetList($folderName) {

	$GenExtra = 0;

	while (count($this->assetsList) > 0):

	    extract($this->assetsList);

	    $run = '$intersectresult = array_intersect($' . implode(',$', $this->list) . ');';
	    eval($run);

	    $justCss = $this->getFileType($intersectresult, 'css');
	    $justJs = $this->getFileType($intersectresult, 'js');

	    $GenExtraTo = $GenExtra != 0 ? $this->nameGenerator(Enum::numToWords($GenExtra)) : '';

	    $assetName = $this->assetGeneral . $GenExtraTo;

	    $this->generalAssetsList[$assetName]['files'] = implode(',', $this->list);
	    $this->generalAssetsList[$assetName]['filesOriginal'] = $this->listOriginal;
	    $this->generalAssetsList[$assetName]['foldername'] = $folderName;
	    $this->generalAssetsList[$assetName]['css'] = $this->getFileType($intersectresult, 'css');
	    $this->generalAssetsList[$assetName]['js'] = $this->getFileType($intersectresult, 'js');

	    $this->layoutsFirstList[$assetName] = $this->listOriginal;
	    $this->layoutsList[] = $assetName;
	    $this->layoutsListAll = ArrayHelper::merge($this->layoutsListAll, $this->list);

	    $GenExtra++;

	    $this->assetClear($intersectresult);

	endwhile;
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
