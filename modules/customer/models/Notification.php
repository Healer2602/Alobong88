<?php

namespace modules\customer\models;

use modules\notification\Mailer;
use modules\notification\models\EmailTemplate;
use Yii;

/**
 * Class Notification
 *
 * @package common\base
 */
final class Notification extends Mailer{

	/**
	 * @param \modules\customer\models\Customer $member
	 *
	 * @return bool
	 */
	public static function forgotPassword(Customer $member){
		if ($email_template = EmailTemplate::findKey('RESET_PASSWORD', Yii::$app->language)){
			$alert_params = $email_params = $email_template->params;

			$alert_params['user:name']           = $member->name;
			$alert_params['user:email']          = $member->email;
			$alert_params['reset_password_link'] = Yii::$app->urlManager->createAbsoluteUrl(['/customer/default/new-password', 'token' => $member->password_reset_token]);

			if (!empty($member->email)){
				$subject = str_replace($email_params, $alert_params, $email_template->subject);
				$body    = str_replace($email_params, $alert_params, $email_template->content);

				return self::send($member->email, $subject, $body);
			}
		}

		return FALSE;
	}

	/**
	 * @param \modules\customer\models\Customer $object
	 * @param $message
	 *
	 * @return bool
	 */
	public static function rejectKyc($object, $message){
		$language = $object->language->language ?? NULL;

		if ($email_template = EmailTemplate::findKey('REJECT_KYC', $language)){
			$alert_params = $email_params = $email_template->params;

			$alert_params['user:name']      = $object->name;
			$alert_params['user:email']     = $object->email;
			$alert_params['reject:message'] = $message;

			if (!empty($object->email)){
				$subject = str_replace($email_params, $alert_params, $email_template->subject);
				$body    = str_replace($email_params, $alert_params, $email_template->content);

				return self::addQueue($object->email, $subject, $body);
			}
		}

		return FALSE;
	}

	/**
	 * @param \modules\customer\models\Customer $object
	 *
	 * @return bool
	 */
	public static function submitNotifyKyc($object){
		$language = $object->language->language ?? NULL;

		if ($email_template = EmailTemplate::findKey('SUBMIT_NOTIFY_KYC', $language)){
			$alert_params = $email_params = $email_template->params;

			$alert_params['user:name']  = $object->name;
			$alert_params['user:email'] = $object->email;

			if (!empty($object->email)){
				$subject = str_replace($email_params, $alert_params, $email_template->subject);
				$body    = str_replace($email_params, $alert_params, $email_template->content);

				return self::addQueue($object->email, $subject, $body);
			}
		}

		return FALSE;
	}

	/**
	 * @param \modules\customer\models\Customer $object
	 *
	 * @return bool
	 */
	public static function approveKyc($object){
		$language = $object->language->language ?? NULL;

		if ($email_template = EmailTemplate::findKey('APPROVE_KYC', $language)){
			$alert_params = $email_params = $email_template->params;

			$alert_params['user:name']  = $object->name;
			$alert_params['user:email'] = $object->email;

			if (!empty($object->email)){
				$subject = str_replace($email_params, $alert_params, $email_template->subject);
				$body    = str_replace($email_params, $alert_params, $email_template->content);

				return self::addQueue($object->email, $subject, $body);
			}
		}

		return FALSE;
	}

	/**
	 * @param \modules\customer\models\Customer $object
	 *
	 * @return bool
	 */
	public static function newCustomer($object){
		if ($email_template = EmailTemplate::findKey('NEW_CUSTOMER', Yii::$app->language)){
			$alert_params = $email_params = $email_template->params;

			$alert_params['player:name']     = $object->name;
			$alert_params['player:username'] = $object->username;
			$alert_params['player:email']    = $object->email;

			if (!empty($object->email)){
				$subject = str_replace($email_params, $alert_params, $email_template->subject);
				$body    = str_replace($email_params, $alert_params, $email_template->content);

				return self::addQueue($object->email, $subject, $body);
			}
		}

		return FALSE;
	}

	/**
	 * @param \modules\customer\models\Customer $object
	 * @param string $code
	 * @param string $token
	 *
	 * @return bool
	 */
	public static function verifyEmail($object, $code, $token)
	: bool{
		$language = $object->language->language ?? NULL;

		if ($email_template = EmailTemplate::findKey('PLAYER_VERIFY_EMAIL', $language)){
			$alert_params = $email_params = $email_template->params;

			$alert_params['player:name']       = $object->name;
			$alert_params['player:email']      = $object->email;
			$alert_params['player:phone']      = $object->phone_number;
			$alert_params['verification:code'] = $code;
			$alert_params['verification:link'] = Yii::$app->urlManager->createAbsoluteUrl(['/customer/default/verify-email', 'token' => $token]);

			if (!empty($object->email)){
				$subject = str_replace($email_params, $alert_params, $email_template->subject);
				$body    = str_replace($email_params, $alert_params, $email_template->content);

				return self::send($object->email, $subject, $body);
			}
		}

		return FALSE;
	}
}