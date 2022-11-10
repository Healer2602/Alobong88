<?php

use common\base\EnvHelper;

$params = array_merge(
	require __DIR__ . '/../../common/config/params.php',
	require __DIR__ . '/../../common/config/params-local.php',
	require __DIR__ . '/params.php',
	require __DIR__ . '/params-local.php'
);

$config = [
	'id'                  => 'console',
	'basePath'            => dirname(__DIR__),
	'bootstrap'           => ['log'],
	'controllerNamespace' => 'console\controllers',
	'aliases'             => [
		'@bower' => '@vendor/bower-asset',
		'@npm'   => '@vendor/npm-asset',
	],
	'controllerMap'       => [
		'fixture' => [
			'class'     => 'yii\console\controllers\FixtureController',
			'namespace' => 'common\fixtures',
		]
	],
	'components'          => [
		'log'        => [
			'targets' => [
				[
					'class'  => 'yii\log\FileTarget',
					'levels' => ['error', 'warning'],
				],
			],
		],
		'urlManager' => [
			'class' => 'common\base\UrlManager'
		]
	],
	'params'              => $params,
];

$config['components']['urlManager']['hostInfo'] = EnvHelper::env('HOME_URL');
$config['components']['urlManager']['baseUrl']  = EnvHelper::env('HOME_URL');

return $config;