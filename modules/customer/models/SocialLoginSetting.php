<?php


namespace modules\customer\models;


use common\models\SettingForm;
use Yii;

/**
 * Class SocialLoginSetting
 *
 * @package modules\wallet\models
 */
class SocialLoginSetting extends SettingForm{

	public $google_client;
	public $google_secret;
	public $facebook_client;
	public $facebook_secret;

	/**
	 * @return array
	 */
	public function rules(){
		return [
			[['google_client', 'google_secret'], 'string'],
			[['facebook_client', 'facebook_secret'], 'string'],
		];
	}

	/**
	 * @return array
	 */
	public function attributeLabels(){
		return [
			'google_client'   => Yii::t('customer', 'Google Client ID'),
			'google_secret'   => Yii::t('customer', 'Google Secret Key'),
			'facebook_client' => Yii::t('customer', 'Facebook Client ID'),
			'facebook_secret' => Yii::t('customer', 'Facebook Secret Key'),
		];
	}
}