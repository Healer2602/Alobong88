<?php

namespace modules\spider\lib\select2;

use yii\web\AssetBundle;

/**
 * Select2 asset bundle.
 */
class FullAsset extends AssetBundle{

	public $sourcePath = '@modules/spider/lib/select2/dist/css';

	public $css = [
		'select2.min.css',
		'select.css',
	];

	public $depends = [
		Asset::class
	];
}
