<?php

namespace modules\media\ckfinder;

use yii\web\AssetBundle;

/**
 * Editor asset bundle.
 */
class FileAsset extends AssetBundle{

	public $sourcePath = '@modules/media/ckfinder/assets';

	public $js = [
		'ckfinder.js',
	];

	public $publishOptions = [
		'forceCopy' => YII_ENV_DEV
	];
}
