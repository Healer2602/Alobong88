<?php

use common\base\EnvHelper;
use common\base\Queue;
use yii\mutex\FileMutex;

$config = [
	'aliases'        => [
		'@bower' => '@vendor/bower-asset',
		'@npm'   => '@vendor/npm-asset',
	],
	'vendorPath'     => dirname(__DIR__, 2) . '/vendor',
	'bootstrap'      => ['queue', 'file_queue'],
	'components'     => [
		'cookies'     => [
			'class'    => 'yii\web\Cookie',
			'httpOnly' => TRUE,
			'secure'   => TRUE
		],
		'security'    => [
			'class'  => 'yii\base\Security',
			'cipher' => 'AES-256-CBC'
		],
		'cache'       => [
			'class'     => 'yii\caching\FileCache',
			'cachePath' => '@cache',
		],
		'urlManager'  => [
			'class'           => 'yii\web\UrlManager',
			'showScriptName'  => FALSE,
			'enablePrettyUrl' => TRUE
		],
		'formatter'   => [
			'class'                    => 'common\base\Formatter',
			'nullDisplay'              => '-',
			'timeZone'                 => 'Asia/Singapore',
			'defaultTimeZone'          => 'Etc/GMT+0',
			'dateFormat'               => 'php:d/m/Y',
			'datetimeFormat'           => 'php:d/m/Y h:i A',
			'currencyCode'             => 'VND',
			'decimalSeparator'         => '.',
			'thousandSeparator'        => ',',
			'currencyDecimalSeparator' => '.',
			// 'numberFormatterSymbols'   => [
			// 	NumberFormatter::CURRENCY_SYMBOL => 'đ',
			// ],
			// 'numberFormatterOptions'   => [
			// 	NumberFormatter::MIN_FRACTION_DIGITS => 0,
			// 	NumberFormatter::MAX_FRACTION_DIGITS => 0,
			// ]
		],
		'i18n'        => [
			'translations' => [
				'common' => [
					'class'    => 'yii\i18n\PhpMessageSource',
					'basePath' => '@common/messages',
					'fileMap'  => [
						'common' => 'common.php',
					],
				]
			],
		],
		'authManager' => [
			'class' => 'common\base\AuthManager',
		],
		'queue'       => [
			'class'          => Queue::class,
			'mutex'          => FileMutex::class,
			'deleteReleased' => TRUE
		],
		'file_queue'  => [
			'class'          => Queue::class,
			'mutex'          => FileMutex::class,
			'deleteReleased' => TRUE,
			'channel'        => Queue::CHANNEL_FILE
		],
		'db'          => [
			'class'               => 'yii\db\Connection',
			'charset'             => 'utf8',
			'enableSchemaCache'   => TRUE,
			'schemaCacheDuration' => 36000,
			'schemaCache'         => 'cache',
		],
		'mailer'      => [
			'class'    => 'modules\gmail\src\Mailer',
			'viewPath' => '@common/mail'
		],
	],
	'language'       => 'en',
	'sourceLanguage' => 'en_US',
	'timeZone'       => 'Asia/Singapore',
];

$config['name'] = EnvHelper::env('APP_NAME', 'Spider');

$db_url = EnvHelper::env('DB_URL');
if (!empty($db_url)){
	$config['components']['db']['dsn'] = $db_url;
}else{
	$db_host = EnvHelper::env('DB_HOST', 'localhost');
	$db_name = EnvHelper::env('DB_NAME');

	$config['components']['db']['dsn'] = "mysql:host={$db_host};dbname={$db_name}";
}

$config['components']['db']['tablePrefix']       = EnvHelper::env('DB_PREFIX');
$config['components']['db']['enableSchemaCache'] = EnvHelper::env('DB_CACHE', TRUE);
$config['components']['db']['username']          = EnvHelper::env('DB_USER');
$config['components']['db']['password']          = EnvHelper::env('DB_PASSWORD');

return yii\helpers\ArrayHelper::merge(
	$config,
	require(__DIR__ . '/modules.php'),
	require(__DIR__ . '/modules-local.php')
);