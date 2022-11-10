<?php

use common\base\EnvHelper;

$params = [
	'user.passwordResetTokenExpire' => 3600,

	'bsVersion'           => '5.x',
	'bsDependencyEnabled' => FALSE,

	'media.license' => '*9?B-*1**-H**5-*H**-*E**-K*K*-2**J',

	'referral' => [
		'prefix' => 'ZZ',
		'length' => 6
	],

	'deployments' => []
];

$params['file.path']       = Yii::getAlias('@files');
$params['file.public_url'] = EnvHelper::env('HOME_URL') . '/files';
$params['file.key']        = EnvHelper::env('ENCRYPTION_KEY_PATH');

$params['recaptcha'] = [
	'site_key'   => EnvHelper::env('RECAPTCHA_SITEKEY'),
	'secret_key' => EnvHelper::env('RECAPTCHA_SECRET'),
];


return $params;