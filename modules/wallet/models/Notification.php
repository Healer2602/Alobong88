<?php

namespace modules\wallet\models;

use modules\notification\Mailer;
use modules\notification\models\EmailTemplate;
use modules\notification\models\TelegramSetting;
use Yii;

/**
 * Class Notification
 *
 * @package common\base
 */
final class Notification extends Mailer{

	/**
	 * @param \modules\wallet\models\Transaction $object
	 *
	 * @return bool
	 * @throws \yii\base\InvalidConfigException
	 */
	public static function withdrawNew($object){
		// Send to Telegram
		self::telegramWithdrawalCompleted($object);

		// Send to Email
		$language = $object->wallet->customer->language->language ?? NULL;

		if ($email_template = EmailTemplate::findKey('WITHDRAW_NEW', $language)){
			$alert_params = $email_params = $email_template->params;
			if ($customer = $object->wallet->customer){
				$params = $object->formatParams();

				$alert_params['customer:name']            = $customer->name;
				$alert_params['withdraw:id']              = $object->transaction_id;
				$alert_params['withdraw:amount']          = Yii::$app->formatter->asCurrency(abs($object->amount));
				$alert_params['withdraw:date']            = Yii::$app->formatter->asDatetime($object->created_at);
				$alert_params['withdraw:fee']             = $params['Fee'] ?? 0;
				$alert_params['withdraw:received_amount'] = Yii::$app->formatter->asDecimal($params['Amount'] ?? 0) . ' ' . ($params['Currency'] ?? '');
				$alert_params['withdraw:address']         = $params['Address'] ?? '';
				$alert_params['withdraw:currency']        = $params['Currency'] ?? '';

				if (!empty($customer->email)){
					$subject = str_replace($email_params, $alert_params, $email_template->subject);
					$body    = str_replace($email_params, $alert_params, $email_template->content);

					return self::addQueue($customer->email, $subject, $body);
				}
			}
		}

		return FALSE;
	}

	/**
	 * @param \modules\wallet\models\Transaction $object
	 *
	 * @return bool
	 * @throws \yii\base\InvalidConfigException
	 */
	public static function withdrawApproved($object){
		// Send to Email
		$language = $object->wallet->customer->language->language ?? NULL;

		if ($email_template = EmailTemplate::findKey('WITHDRAW_APPROVED', $language)){
			$alert_params = $email_params = $email_template->params;
			if ($customer = $object->wallet->customer){
				$alert_params['customer:name']            = $customer->name;
				$alert_params['withdraw:id']              = $object->transaction_id;
				$alert_params['withdraw:amount']          = Yii::$app->formatter->asCurrency(abs($object->amount));
				$alert_params['withdraw:date']            = Yii::$app->formatter->asDatetime($object->created_at);
				$alert_params['withdraw:fee']             = $params['Fee'] ?? 0;
				$alert_params['withdraw:received_amount'] = Yii::$app->formatter->asDecimal($params['Amount'] ?? 0) . ' ' . ($params['Currency'] ?? '');
				$alert_params['withdraw:address']         = $params['Address'] ?? '';
				$alert_params['withdraw:currency']        = $params['Currency'] ?? '';

				if (!empty($customer->email)){
					$subject = str_replace($email_params, $alert_params, $email_template->subject);
					$body    = str_replace($email_params, $alert_params, $email_template->content);

					return self::addQueue($customer->email, $subject, $body);
				}
			}
		}

		return FALSE;
	}

	/**
	 * @param \modules\wallet\models\Transaction $object
	 *
	 * @return bool
	 * @throws \yii\base\InvalidConfigException
	 */
	public static function withdrawRejected($object){
		$language = $object->wallet->customer->language->language ?? NULL;

		if ($email_template = EmailTemplate::findKey('WITHDRAW_REJECTED', $language)){
			$alert_params = $email_params = $email_template->params;
			if ($customer = $object->wallet->customer){
				$alert_params['customer:name']   = $customer->name;
				$alert_params['withdraw:id']     = $object->transaction_id;
				$alert_params['withdraw:amount'] = Yii::$app->formatter->asCurrency($object->amount);
				$alert_params['withdraw:date']   = Yii::$app->formatter->asDatetime($object->created_at);

				if (!empty($customer->email)){
					$subject = str_replace($email_params, $alert_params, $email_template->subject);
					$body    = str_replace($email_params, $alert_params, $email_template->content);

					return self::addQueue($customer->email, $subject, $body);
				}
			}
		}

		return FALSE;
	}

	/**
	 * @param \modules\wallet\models\Transaction $object
	 *
	 * @return bool
	 * @throws \yii\base\InvalidConfigException
	 */
	public static function newDeposit($object){
		$language = $object->wallet->customer->language->language ?? NULL;

		if ($email_template = EmailTemplate::findKey('NEW_DEPOSIT', $language)){
			if ($customer = $object->wallet->customer){
				$alert_params = $email_params = $email_template->params;
				$params       = $object->formatParams();

				$alert_params['deposit:id']       = $object->transaction_id;
				$alert_params['deposit:date']     = Yii::$app->formatter->asDatetime($object->created_at);
				$alert_params['deposit:amount']   = Yii::$app->formatter->asCurrency($object->amount);
				$alert_params['deposit:received'] = Yii::$app->formatter->asCurrency($object->amount);
				$alert_params['deposit:gateway']  = $params['Gateway'] ?? '-';

				if (!empty($customer->email)){
					$subject = str_replace($email_params, $alert_params, $email_template->subject);
					$body    = str_replace($email_params, $alert_params, $email_template->content);

					return self::addQueue($customer->email, $subject, $body);
				}
			}
		}

		return FALSE;
	}

	/**
	 * @param \modules\wallet\models\Transaction $transaction
	 *
	 * @return void
	 */
	public static function telegramWithdrawNew($transaction){
		$setting = new TelegramSetting();
		$setting->getValues();

		$tele_message = "New Withdrawal Request needs to be approved: <strong>{$transaction->transaction_id}</strong>";
		if (!empty($setting->telegram_withdraw_mention)){
			$tele_message .= "\n\n" . $setting->telegram_withdraw_mention;
		}

		TelegramSetting::addQueue($tele_message);
	}

	/**
	 * @param \modules\wallet\models\Transaction $transaction
	 *
	 * @return void
	 */
	public static function telegramWithdrawalCompleted($transaction){
		$setting = new TelegramSetting();
		$setting->getValues();

		$tele_message = "Withdrawal Request has been completed: <strong>{$transaction->transaction_id}</strong>";
		if (!empty($setting->telegram_withdraw_mention)){
			$tele_message .= "\n\n" . $setting->telegram_withdraw_mention;
		}

		TelegramSetting::addQueue($tele_message);
	}
}