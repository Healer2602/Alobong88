<?php

use frontend\base\View;
use modules\customer\frontend\models\CustomerIdentity;
use modules\customer\models\Session;
use yii\bootstrap5\LinkPager;
use yii\web\DbSession;
use yii\web\UrlNormalizer;
use yii\web\User;

$params = array_merge(
	require __DIR__ . '/../../common/config/params.php',
	require __DIR__ . '/../../common/config/params-local.php',
	require __DIR__ . '/params.php',
	require __DIR__ . '/params-local.php'
);

return [
	'id'                  => 'website',
	'basePath'            => dirname(__DIR__),
	'bootstrap'           => ['log'],
	'controllerNamespace' => 'frontend\controllers',
	'components' => [
		'request'              => [
			'csrfParam' => '_captain',
		],
		'user'                 => [
			'identityClass'   => CustomerIdentity::class,
			'enableAutoLogin' => TRUE,
			'identityCookie'  => ['name' => '_spider_play', 'httpOnly' => TRUE],
			'class'           => User::class
		],
		'session'              => [
			'class'         => DbSession::class,
			'name'          => '_spider_ses',
			'sessionTable'  => Session::tableName(),
			'timeout'       => 2880,
			'writeCallback' => function (){
				return [
					'customer_id' => Yii::$app->user->id
				];
			},
		],
		'log'                  => [
			'traceLevel' => YII_DEBUG ? 3 : 0,
			'targets'    => [
				[
					'class'  => 'yii\log\FileTarget',
					'levels' => ['error', 'warning'],
				],
			],
		],
		'errorHandler'         => [
			'errorAction' => 'site/error',
		],
		'view'                 => [
			'class' => View::class,
			'theme' => [
				'basePath' => '@modules/themes/captain',
				'pathMap'  => [
					'@frontend/views'   => '@modules/themes/captain',
					'@frontend/widgets' => '@modules/themes/captain/widgets'
				],
			],
		],
		'urlManager'           => [
			'class'      => 'common\base\UrlManager',
			'normalizer' => [
				'class'  => 'yii\web\UrlNormalizer',
				'action' => UrlNormalizer::ACTION_REDIRECT_TEMPORARY,
			],
			'rules'      => [
				'/'                         => 'site/index',
				'site/<action:[a-z0-9\-]+>' => 'site/<action>',
			],
		],
		'assetManager'         => [
			'class'   => 'yii\web\AssetManager',
			'bundles' => [
				'yii\web\JqueryAsset'                 => [
					'js' => [
						YII_ENV_DEV ? 'jquery.js' : 'jquery.min.js'
					]
				],
				'yii\bootstrap5\BootstrapAsset'       => [
					'css' => [
						YII_ENV_DEV ? 'css/bootstrap.css' : 'css/bootstrap.min.css',
					]
				],
				'yii\bootstrap5\BootstrapPluginAsset' => [
					'js' => [
						YII_ENV_DEV ? 'js/bootstrap.bundle.js' : 'js/bootstrap.bundle.min.js',
					]
				],
			],
		],
		'authClientCollection' => [
			'class'   => 'yii\authclient\Collection',
			'clients' => [
				'google'   => [
					'class' => 'yii\authclient\clients\Google'
				],
				'facebook' => [
					'class' => 'yii\authclient\clients\Facebook'
				],
			],
		],
		'i18n'                 => [
			'translations' => [
				'common' => [
					'class' => 'common\base\MessageSource',
				],
			],
		],
	],
	'params'     => $params,
	'container'  => [
		'definitions' => [
			\yii\widgets\LinkPager::class => [
				'class'          => LinkPager::class,
				'options'        => ['class' => 'nav-pagination'],
				'maxButtonCount' => 8
			],
			\yii\data\Pagination::class   => [
				'class'           => \yii\data\Pagination::class,
				'defaultPageSize' => 10
			]
		],
	],
];
