<?php

namespace modules\themes\captain;

use modules\spider\lib\lineawesome\Asset;
use modules\spider\lib\mmenu\MmenuAsset;
use modules\spider\lib\select2\FullAsset;
use yii\bootstrap5\BootstrapPluginAsset;
use yii\web\AssetBundle;
use yii\web\YiiAsset;

/**
 * Main frontend application asset bundle.
 */
class AppAsset extends AssetBundle{

	public $sourcePath = '@modules/themes/captain/assets';

	public $css = [
		'https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,wght@0,400;0,600;0,700;1,400;1,600;1,700&display=swap',
		'css/main.css?v3.1',
	];

	public $js = [
		'js/main.min.js?v2.1',
	];

	public $depends = [
		YiiAsset::class,
		BootstrapPluginAsset::class,
		Asset::class,
		FullAsset::class,
		\modules\spider\lib\flatpickr\Asset::class,
		\modules\spider\lib\slick\Asset::class,
		MmenuAsset::class,
	];

	public $publishOptions = [
		'forceCopy' => YII_ENV_DEV
	];
}
