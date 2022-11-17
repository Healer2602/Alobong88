<?php

namespace modules\website\assets;

use yii\web\AssetBundle;

/**
 * Class MMenuAsset
 *
 * @package modules\website\assets
 */
class MMenuAsset extends AssetBundle{

	public $sourcePath = '@modules/website/assets/mmenu';

	public $js = [
		'mmenu.min.js'
	];

	public $css = [
		'mmenu.css'
	];

}