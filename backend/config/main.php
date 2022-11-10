<?php

use backend\base\ActiveField;
use backend\base\View;
use yii\bootstrap5\LinkPager;
use yii\data\ActiveDataProvider;

$params = array_merge(
	require __DIR__ . '/../../common/config/params.php',
	require __DIR__ . '/../../common/config/params-local.php',
	require __DIR__ . '/params.php',
	require __DIR__ . '/params-local.php'
);

return [
	'id'                  => 'admin',
	'basePath'            => dirname(__DIR__),
	'controllerNamespace' => 'backend\controllers',
	'bootstrap'           => ['log'],
	'components'          => [
		'request'      => [
			'csrfParam' => '_csrf-admin',
		],
		'user'         => [
			'identityClass'   => 'backend\models\Staff',
			'enableAutoLogin' => TRUE,
			'identityCookie'  => ['name' => '_spider-admin', 'httpOnly' => TRUE],
		],
		'session'      => [
			// this is the name of the session cookie used for login on the backend
			'name' => 'spider-admin',
		],
		'log'          => [
			'traceLevel' => YII_DEBUG ? 3 : 0,
			'targets'    => [
				[
					'class'  => 'yii\log\FileTarget',
					'levels' => ['error', 'warning'],
				],
			],
		],
		'errorHandler' => [
			'errorAction' => 'site/error',
		],
		'urlManager'   => [
			'rules' => [
				'<controller:[a-z0-9\-]+>/<id:\d+>'                      => '<controller>/view',
				'<controller:[a-z0-9\-]+>'                               => '<controller>/index',
				'<controller:[a-z0-9\-]+>/<action:[a-z0-9\-]+>/<id:\d+>' => '<controller>/<action>',
				'<controller:[a-z0-9\-]+>/<action:[a-z0-9\-]+>'          => '<controller>/<action>',

				'<module:[a-z0-9\-]+>/<controller:[a-z0-9\-]+>'                               => '<module>/<controller>/index',
				'<module:[a-z0-9\-]+>/<controller:[a-z0-9\-]+>/<action:[a-z0-9\-]+>/<id:\d+>' => '<module>/<controller>/<action>',
				'<module:[a-z0-9\-]+>/<controller:[a-z0-9\-]+>/<action:[a-z0-9\-]+>'          => '<module>/<controller>/<action>',
			],
		],
		'assetManager' => [
			'class'   => 'yii\web\AssetManager',
			'bundles' => [
				'yii\web\JqueryAsset'           => [
					'js' => [
						YII_ENV_DEV ? 'jquery.js' : 'jquery.min.js'
					]
				],
				'yii\bootstrap5\BootstrapAsset' => [
					'css' => []
				],
			],
		],
		'view'         => [
			'class' => View::class,
			'theme' => [
				'basePath' => '@modules/themes/spiderboard/views',
				'pathMap'  => [
					'@backend/views'   => '@modules/themes/spiderboard/views',
					'@backend/widgets' => '@modules/themes/spiderboard/views/widgets'
				],
			],
		],
	],
	'params'              => $params,
	'container'           => [
		'definitions' => [
			\yii\widgets\LinkPager::class      => LinkPager::class,
			\yii\bootstrap5\ActiveField::class => ActiveField::class,
			ActiveDataProvider::class          => [
				'class'      => ActiveDataProvider::class,
				'pagination' => [
					'pageSizeLimit' => [1, 100]
				]
			]
		],
	],
];