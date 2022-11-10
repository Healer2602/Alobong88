<?php

namespace modules\spider\lib\multiselect;

use yii\web\AssetBundle;
use yii\web\JqueryAsset;

/**
 * Editor asset bundle.
 */
class Asset extends AssetBundle{

	public $sourcePath = '@modules/spider/lib/multiselect';

	public $js = [
		'js/bootstrap-multiselect.min.js',
	];

	public $css = [
		'css/bootstrap-multiselect.css',
	];

	public $depends = [
		JqueryAsset::class
	];

	public $publishOptions = [
		'forceCopy' => YII_ENV_DEV
	];
}
