<?php

namespace modules\themes\spiderboard;

use modules\spider\lib\lineawesome\Asset;
use modules\spider\lib\tinymce\EditorAsset;
use yii\bootstrap5\BootstrapPluginAsset;
use yii\web\AssetBundle;
use yii\web\YiiAsset;

/**
 * Main backend application asset bundle.
 */
class AppAsset extends AssetBundle{

	public $sourcePath = '@modules/themes/spiderboard/assets';

	public $css = [
		'css/theme.bundle.css',
		'css/main.css',
	];

	public $js = [
		'js/theme.bundle.js',
		'js/clipboard.min.js',
		'js/main.min.js',
	];

	public $depends = [
		YiiAsset::class,
		BootstrapPluginAsset::class,
		EditorAsset::class,
		Asset::class,
		\modules\spider\lib\select2\Asset::class,
		\modules\spider\lib\flatpickr\Asset::class
	];

	public $publishOptions = [
		'forceCopy' => YII_ENV_DEV
	];
}
