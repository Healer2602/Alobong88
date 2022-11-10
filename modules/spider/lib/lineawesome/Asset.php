<?php

namespace modules\spider\lib\lineawesome;

use yii\web\AssetBundle;

/**
 * LineAwesome asset bundle.
 */
class Asset extends AssetBundle{

	public $sourcePath = '@modules/spider/lib/lineawesome/static';

	public $css = [
		'css/line-awesome.min.css',
	];
}
