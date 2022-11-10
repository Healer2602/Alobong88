<?php

namespace modules\website\models;

use common\models\SettingForm;
use Yii;


/**
 * Class WebsiteSetting
 *
 * @package modules\website\models
 *
 * @property string $logoLink
 */
class WebsiteSetting extends SettingForm{

	public $site_logo;
	public $site_favicon;
	public $social_image;
	public $admin_email;
	public $gtm;

	/**
	 * @return array
	 */
	public function rules(){
		return [
			[['site_logo', 'site_favicon', 'social_image', 'gtm'], 'string'],
			[['site_logo', 'admin_email'], 'required'],
			['admin_email', 'email']
		];
	}

	/**
	 * @return array
	 */
	public function attributeLabels(){
		return [
			'site_favicon' => Yii::t('common', 'Favicon'),
			'site_logo'    => Yii::t('common', 'Logo'),
			'social_image' => Yii::t('common', 'Social Image'),
			'admin_email'  => Yii::t('common', 'Admin Email'),
			'gtm'          => Yii::t('common', 'Google Tag Manager'),
		];
	}
}