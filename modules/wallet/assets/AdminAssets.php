<?php

namespace modules\wallet\assets;

/**
 * Class AdminAssets
 *
 * @package modules\wallet\assets
 */
class AdminAssets extends Assets{

	public $css = [
		'css/style.css',
	];

	public $depends = [
		Assets::class
	];
}