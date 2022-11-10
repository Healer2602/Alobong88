<?php

namespace modules\spider\lib\select2;

use yii\web\AssetBundle;
use yii\web\JqueryAsset;

/**
 * Select2 asset bundle.
 */
class Asset extends AssetBundle{

	public $sourcePath = '@modules/spider/lib/select2/dist';

	public $js = [
		'js/select2.full.min.js',
		'select2.min.js',
		'tags.min.js',
	];

	public $depends = [
		JqueryAsset::class
	];

	public $publishOptions = [
		'forceCopy' => YII_ENV_DEV
	];
}
