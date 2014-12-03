<?php

namespace serhatozles\elfinder;

// Full Namespace : serhatozles\elfinder\elFinder

use Yii;
use yii\helpers\Url;

class elFinder extends \yii\base\Widget {

    public $ajax;
    public $controller = 'elfinder';
    public $language;
    public $height = 420;
    public $options = [];
    protected $elFinder;

    public function run() {

	if (!empty($this->ajax)) {
	    echo '<iframe src="' . Url::to([$this->controller . '/control', 'ajax' => $this->ajax]) . '" style="border: 0; width: 100%; height: ' . $this->height . 'px;"></iframe>';
	}
    }

    public function connector($options = null) {

	if (is_null($options)) {
	    $options = [
		'roots' => [
		    [
			'driver' => 'LocalFileSystem', // driver for accessing file system (REQUIRED)
			'path' => Yii::getAlias('@webroot/files/'), // path to files (REQUIRED)
			'URL' => Yii::getAlias('@web/files/'), // URL to files (REQUIRED)
		    ],
		]
	    ];
	}

	$options['roots'][0]['attributes'][] = [
		    'pattern' => '@(tmb|\.quarantine)@si',
		    'read' => false,
		    'write' => false,
		    'hidden' => true,
		    'locked' => true
	];

	return $this->renderFile(__DIR__ . "/views/connect.php", ['options' => $options]);
    }

}
