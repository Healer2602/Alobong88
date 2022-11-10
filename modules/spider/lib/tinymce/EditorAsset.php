<?php

namespace modules\spider\lib\tinymce;

use yii\web\AssetBundle;
use yii\web\JqueryAsset;

/**
 * Editor asset bundle.
 */
class EditorAsset extends AssetBundle{

	public $sourcePath = '@modules/spider/lib/tinymce/static';

	public $js = [
		'tinymce.min.js',
		'config.min.js',
		'init.min.js',
	];

	public $depends = [
		JqueryAsset::class
	];
}
