<?php

namespace modules\spider\lib\mmenu;

use yii\web\AssetBundle;
use yii\web\JqueryAsset;

/**
 * MmenuAsset asset bundle.
 */
class MmenuAsset extends AssetBundle{

	public $sourcePath = '@modules/spider/lib/mmenu';

	public $js = [
		'js/mmenu.js',
	];

	public $depends = [
		JqueryAsset::class
	];

	public $publishOptions = [
		'forceCopy' => YII_ENV_DEV
	];
}
