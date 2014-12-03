<?php

namespace serhatozles\themeintegrator;

use Yii;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\web\Controller as BaseController;

class Controller extends BaseController {

    public $assetsList = [];
    public $list = [];
    private $assetTemplate = '/template/Asset.txt';
    private $assetGeneral = 'App';

    const ASSETNAME = "ASSETNAME";
    const ASSETCSSLIST = "ASSETCSSLIST";
    const ASSETJSLIST = "ASSETJSLIST";
    const ASSETFOLDER = "ASSETFOLDER";

    public function actionDefine() {

	if (Yii::$app->request->isPost) {

	    $post = Yii::$app->request->post();

	    $folder = Yii::getAlias('@app/template/') . $post['folder'];
	    $fileList = $this->getHtml($folder);

//	    for ($i = 0; $i < count($fileList); $i++):
	    for ($i = 0; $i < 2; $i++):

		$HtmlFile = file_get_contents($folder . '/' . $fileList[$i]);
		$filename = pathinfo($fileList[$i]);
		$genfilename = $this->nameGenerator($filename['filename']);
		$this->list[] = $genfilename;
		$this->assetsList[$genfilename] = $this->getAssets($HtmlFile);

	    endfor;

	    extract($this->assetsList);

	    $run = '$intersectresult = array_intersect($' . implode(',$', $this->list) . ');';
	    eval($run);

	    $justCss = $this->getFileType($intersectresult, 'css');
	    $justJs = $this->getFileType($intersectresult, 'js');

	    $rsjustCss = "\r\n" . implode(',' . "\r\n", array_map(function ($str) {
				return "'" . $str . "'";
			    }, $justCss)) . "\r\n";
	    $rsjustJs = "\r\n" . implode(',' . "\r\n", array_map(function ($str) {
				return "'" . $str . "'";
			    }, $justJs)) . "\r\n";

	    $AssetTemplate = $this->TemplateOpen($this->assetTemplate);
	    $AssetTemplate = $this->changeAsset($AssetTemplate, $this->assetGeneral, ASSETNAME);
	    $AssetTemplate = $this->changeAsset($AssetTemplate, $rsjustCss, ASSETCSSLIST);
	    $AssetTemplate = $this->changeAsset($AssetTemplate, $rsjustJs, ASSETJSLIST);




	    echo "<textarea style='width:1500px;height:700px;'>";
	    print_r($AssetTemplate);
	    echo "</textarea>";

	    return "";
	}

	$themeList = $this->dirToArray(Yii::getAlias('@app/template'));
	$themeList = ArrayHelper::map($themeList, 'theme', 'theme');

	return $this->renderFile(__DIR__ . "/views/client.php", ['themeList' => $themeList]);
    }

    function changeAsset($Template, $Change, $To) {

	return str_replace('{' . $To . '}', $Change, $Template);
    }

    function TemplateOpen($Template) {

	return file_get_contents(__DIR__ . $Template);
    }

    function getAssets($Source) {

	$html = \serhatozles\simplehtmldom\SimpleHTMLDom::str_get_html($Source);
	$Links = [];

	foreach ($html->find('link[rel=stylesheet]') as $link) {

	    $Links[] = $link->href;
	}

	foreach ($html->find('script') as $link) {

	    $Links[] = $link->src;
	}

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

}
