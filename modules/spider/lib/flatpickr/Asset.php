<?php

namespace modules\spider\lib\flatpickr;

use yii\web\AssetBundle;
use yii\web\JqueryAsset;

/**
 * Focus theme js Calendar bundle.
 */
class Asset extends AssetBundle{

	public $sourcePath = '@modules/spider/lib/flatpickr/static';

	public $css = [
		'flatpickr.min.css',
	];

	public $js = [
		'flatpickr.min.js',
		'theme.flatpickr.min.js',
	];

	public $depends = [
		JqueryAsset::class
	];
}
