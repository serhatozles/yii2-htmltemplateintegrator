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
class HTMLIntegratorBootstrapAsset extends \yii\web\AssetBundle
{
    
    public $sourcePath = '@vendor/serhatozles/yii2-htmltemplateintegrator/assets';
    public $css = [
        'css/bootstrap.min.css',
    ];
    public $js = [
    ];
    public $depends = [
	'yii\bootstrap\BootstrapAsset'
    ];

}