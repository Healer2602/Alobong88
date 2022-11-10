<?php

namespace modules\media;

use yii\bootstrap5\BootstrapAsset;
use yii\jui\JuiAsset;
use yii\web\AssetBundle;

/**
 * Class MediaInputAssets
 *
 * @package modules\media\backend
 */
class MediaInputAssets extends AssetBundle{

	public $sourcePath = '@modules/media/backend/assets';

	public $css = [
		'css/input.css',
	];

	public $js = [
		'js/input.min.js',
	];

	public $publishOptions = [
		'forceCopy' => YII_ENV_DEV
	];

	public $depends = [
		BootstrapAsset::class,
		JuiAsset::class
	];
}