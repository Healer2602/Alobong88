<?php

namespace modules\notification;

use common\models\AuditTrail;
use Exception;
use modules\notification\models\EmailSetting;
use Yii;
use yii\base\BaseObject;


/**
 * Class Mailer
 *
 * @package modules\notification
 */
class Mailer extends BaseObject{

	/**
	 * @param $to
	 * @param $subject
	 * @param $body_html
	 * @param string $body_plain
	 * @param null $attachment
	 * @param string $reply_to
	 * @param array $ccs
	 * @param array $bccs
	 *
	 * @return bool
	 */
	public static function send(
		$to,
		$subject,
		$body_html,
		$body_plain = '',
		$attachment = NULL,
		$reply_to = '',
		$ccs = [],
		$bccs = []){

		try{
			$params['[site:name]'] = Yii::$app->name;
			$params['[site:url]']  = Yii::$app->urlManager->createAbsoluteUrl(['']);

			$subject    = str_replace(array_keys($params), $params, $subject);
			$body_plain = str_replace(array_keys($params), $params, $body_plain);
			$body_html  = str_replace(array_keys($params), $params, $body_html);

			$setting = new EmailSetting();
			$setting->getValues();

			/**@var \yii\swiftmailer\Mailer $mailer */
			$mailer = Yii::$app->mailer;
			if ($mailer instanceof \yii\swiftmailer\Mailer){
				$mailer->setTransport([
					'class'      => 'Swift_SmtpTransport',
					'host'       => $setting->email_smtp_server,
					'username'   => $setting->email_smtp_username,
					'password'   => $setting->email_smtp_password,
					'port'       => $setting->email_smtp_port,
					'encryption' => strtolower($setting->email_smtp_protocol),
				]);
			}

			$mailer = $mailer->compose()
			                 ->setFrom([$setting->email_sender => $setting->email_sender_name])
			                 ->setTo($to)
			                 ->setSubject($subject)
			                 ->setTextBody($body_plain)
			                 ->setHtmlBody($body_html);


			if (!empty($reply_to)){
				$mailer->setReplyTo($reply_to);
			}

			if (!empty($ccs)){
				$mailer->setCc($ccs);
			}

			if (!empty($setting->admin_email)){
				$bccs[] = $setting->admin_email;
			}

			if (!empty($bccs)){
				$mailer->setBcc($bccs);
			}

			if (!empty($attachment)){
				if (!is_array($attachment)){
					$attachment = [$attachment];
				}

				foreach ($attachment as $attach){
					if (!empty($attach['content'])){
						$content = $attach['content'];
						unset($attach['content']);
						$mailer->attachContent($content, $attach);
					}else{
						$mailer->attach($attach);
					}
				}
			}

			return $mailer->send();

		}catch (Exception $exception){
			Yii::error($exception->getMessage(), __METHOD__);
			AuditTrail::log('Email', $exception->getMessage(), Yii::t('common', 'Notification'),
				NULL, NULL,
				Yii::$app->id);
		}

		return FALSE;
	}

	/**
	 * @param $email
	 * @param $subject
	 * @param $body
	 *
	 * @return bool
	 */
	public static function addQueue($email, $subject, $body){
		/**@var \yii\queue\db\Queue $queue */
		$queue = Yii::$app->queue;

		$queue->push(new EmailJob([
			'to'      => $email,
			'subject' => $subject,
			'body'    => $body
		]));

		return TRUE;
	}
}