<?php

namespace modules\spider\lib\sortable;

use yii\web\AssetBundle;

/**
 * Sortable asset bundle for backend application.
 */
class SortableAsset extends AssetBundle{

	public $sourcePath = '@modules/spider/lib/sortable/js';

	public $js = [
		'jquery.sortable.min.js',
		'sortablelist.js',
	];

	public $depends = [
		'yii\web\YiiAsset',
		'yii\jui\JuiAsset'
	];
}
