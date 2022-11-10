<?php

namespace modules\media;

use modules\media\ckfinder\FileAsset;
use yii\web\AssetBundle;

/**
 * Class MediaAssets
 *
 * @package modules\media\backend
 */
class MediaAssets extends AssetBundle{

	public $sourcePath = '@modules/media/backend/assets';

	public $js = [
		'js/jquery-2.1.1.min.js',
		'js/main.min.js',
	];

	public $publishOptions = [
		'forceCopy' => YII_ENV_DEV
	];

	public $depends = [
		FileAsset::class
	];
}