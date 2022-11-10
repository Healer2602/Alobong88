<?php

namespace modules\notification\models;

use common\models\SettingForm;
use Yii;

/**
 * Class EmailSetting
 *
 * @package modules\notification\models
 */
class EmailSetting extends SettingForm{

	public $email_smtp_server;
	public $email_smtp_port;
	public $email_smtp_protocol;
	public $email_smtp_username;
	public $email_smtp_password;
	public $email_sender;
	public $email_sender_name;
	public $email_tester;
	public $email_html = 10;
	public $admin_email;

	/**
	 * @return array
	 */
	public function rules(){
		return [
			[['email_smtp_server', 'email_smtp_port', 'email_sender', 'email_sender_name'], 'required'],
			[['email_smtp_server', 'email_sender', 'email_sender_name', 'email_tester', 'email_smtp_username', 'email_smtp_password', 'email_smtp_protocol'], 'string'],
			[['email_smtp_port', 'email_html'], 'integer'],
		];
	}

	/**
	 * @return array
	 */
	public function attributeLabels(){
		return [
			'email_smtp_server'   => Yii::t('common', 'SMTP Server'),
			'email_smtp_port'     => Yii::t('common', 'SMTP Port'),
			'email_smtp_protocol' => Yii::t('common', 'Protocol'),
			'email_smtp_username' => Yii::t('common', 'Username'),
			'email_smtp_password' => Yii::t('common', 'Password'),
			'email_sender'        => Yii::t('common', 'Email from address'),
			'email_sender_name'   => Yii::t('common', 'Email from name'),
			'email_tester'        => Yii::t('common', 'Email to send a test mail'),
			'email_html'          => Yii::t('common', 'Allow to send emails formatted as HTML'),
		];
	}
}