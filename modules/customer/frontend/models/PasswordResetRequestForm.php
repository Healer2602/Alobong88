<?php

namespace modules\customer\frontend\models;

use modules\customer\models\Notification;
use Yii;
use yii\base\Model;

/**
 * Password reset request form
 */
class PasswordResetRequestForm extends Model{

	public $email;


	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			['email', 'trim'],
			['email', 'required'],
			['email', 'email'],
			['email', 'exist',
				'targetClass' => CustomerIdentity::class,
				'filter'      => ['status' => CustomerIdentity::STATUS_ACTIVE],
				'message'     => Yii::t('common', 'There is no user with this email address.')
			],
		];
	}

	/**
	 * Sends an email with a link, for resetting the password.
	 *
	 * @return bool whether the email was send
	 * @throws \yii\base\Exception
	 */
	public function sendEmail(){
		/* @var CustomerIdentity $user */
		$user = CustomerIdentity::findOne([
			'status' => CustomerIdentity::STATUS_ACTIVE,
			'email'  => $this->email,
		]);

		if (!$user){
			return FALSE;
		}

		if (!CustomerIdentity::isPasswordResetTokenValid($user->password_reset_token)){
			$user->generatePasswordResetToken();
			if (!$user->save()){
				return FALSE;
			}
		}

		return Notification::forgotPassword($user);
	}
}
