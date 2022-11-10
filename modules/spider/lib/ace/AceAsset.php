<?php

namespace modules\spider\lib\ace;

use yii\web\AssetBundle;

/**
 * Class AceAsset
 *
 * @package modules\spider\lib\ace
 */
class AceAsset extends AssetBundle{

	public $sourcePath = '@modules/spider/lib/ace/static';

	public $js = [
		'ace.min.js'
	];

	public $css = [
		'ace.css'
	];
}