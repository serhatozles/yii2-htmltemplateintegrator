<?php

/**
 * @copyright Copyright &copy; Serhat OZLES, nippy.in, 2014
 * @package yii2-htmltemplateintegrator
 * @version 1.0.0
 */

namespace serhatozles\themeintegrator;

/**
 * HTMLTemplateIntegrator bundle for \serhatozles\themeintegrator.
 *
 * @author Serhat OZLES <serhatozles@gmail.com>
 * @since 1.0
 */
class HTMLIntegratorAsset extends \yii\web\AssetBundle
{
    
    public $sourcePath = '@vendor/serhatozles/yii2-htmltemplateintegrator/ace';
    public $css = [
//        'jquery.textcomplete.css',
    ];
    public $js = [
	'ace/ace.js',
//	'ace/theme-twilight.js',
	'ace/mode-php.js',
	'jquery-ace.min.js',
    ];
    public $depends = [
	'yii\web\YiiAsset',
    ];

}