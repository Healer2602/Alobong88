<?php

namespace modules\wallet\assets;

use yii\bootstrap5\BootstrapAsset;
use yii\web\AssetBundle;

/**
 * Class Assets
 *
 * @package modules\wallet\assets
 */
class Assets extends AssetBundle{

	public $sourcePath = '@modules/wallet/assets/static';

	public $js = [
		'js/wallet.min.js',
	];

	public $depends = [
		BootstrapAsset::class
	];

	public $publishOptions = [
		'forceCopy' => YII_ENV_DEV
	];
}