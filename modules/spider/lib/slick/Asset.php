<?php

namespace modules\spider\lib\slick;

use yii\web\AssetBundle;
use yii\web\JqueryAsset;

/**
 * Select2 asset bundle.
 */
class Asset extends AssetBundle{

	public $sourcePath = '@modules/spider/lib/slick';

	public $js = [
		'js/slick.min.js',
	];

	public $depends = [
		JqueryAsset::class
	];

	public $publishOptions = [
		'forceCopy' => YII_ENV_DEV
	];
}
