<?php

namespace backend\models;

use backend\models\Staff as User;
use modules\notification\Mailer;
use modules\notification\models\EmailTemplate;
use Yii;
use yii\base\Model;

/**
 * Password reset request form
 */
class ForgotPasswordForm extends Model{

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
				'targetClass' => User::class,
				'filter'      => ['status' => User::STATUS_ACTIVE],
				'message'     => Yii::t('app', 'There is no user with this email address.')
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
		/* @var User $user */
		$user = User::findOne([
			'status' => User::STATUS_ACTIVE,
			'email'  => $this->email,
		]);

		if (!$user){
			return FALSE;
		}

		if (!User::isPasswordResetTokenValid($user->password_reset_token)){
			$user->generatePasswordResetToken();
			if (!$user->save()){
				return FALSE;
			}
		}

		if ($email_template = EmailTemplate::findKey('RESET_PASSWORD')){
			$alert_params                        = $email_params = $email_template->params;
			$alert_params['user:name']           = $user->name;
			$alert_params['user:email']          = $user->email;
			$alert_params['reset_password_link'] = Yii::$app->urlManager->createAbsoluteUrl(['/site/new-password', 'token' => $user->password_reset_token]);

			if (!empty($user->email)){
				$subject = str_replace($email_params, $alert_params, $email_template->subject);
				$body    = str_replace($email_params, $alert_params, $email_template->content);

				return Mailer::send($user->email, $subject, $body);
			}
		}

		return FALSE;
	}
}
