<?php

namespace modules\agent\models;

use modules\notification\Mailer;
use modules\notification\models\EmailTemplate;

/**
 * Class Notification
 *
 * @package common\base
 */
final class Notification extends Mailer{

	/**
	 * @param \modules\agent\models\Agent $object
	 *
	 * @return bool
	 */
	public static function approve($object){
		if ($email_template = EmailTemplate::findKey('AGENT_APPROVE')){
			$alert_params = $email_params = $email_template->params;
			if ($object->email){
				$alert_params['agent:name']  = $object->name;
				$alert_params['agent:email'] = $object->email;
				$alert_params['agent:code']  = $object->code;

				$subject = str_replace($email_params, $alert_params, $email_template->subject);
				$body    = str_replace($email_params, $alert_params, $email_template->content);

				return self::addQueue($object->email, $subject, $body);
			}
		}

		return FALSE;
	}

	/**
	 * @param \modules\agent\models\Agent $object
	 *
	 * @return bool
	 */
	public static function reject($object){
		if ($email_template = EmailTemplate::findKey('AGENT_REJECT')){
			$alert_params = $email_params = $email_template->params;
			if ($object->email){
				$alert_params['agent:name']  = $object->name;
				$alert_params['agent:email'] = $object->email;
				$alert_params['agent:code']  = $object->code;

				$subject = str_replace($email_params, $alert_params, $email_template->subject);
				$body    = str_replace($email_params, $alert_params, $email_template->content);

				return self::addQueue($object->email, $subject, $body);
			}
		}

		return FALSE;
	}

	/**
	 * @param \modules\agent\models\Agent $object
	 *
	 * @return bool
	 */
	public static function block($object){
		if ($email_template = EmailTemplate::findKey('AGENT_BLOCK')){
			$alert_params = $email_params = $email_template->params;
			if ($object->email){
				$alert_params['agent:name']  = $object->name;
				$alert_params['agent:email'] = $object->email;
				$alert_params['agent:code']  = $object->code;

				$subject = str_replace($email_params, $alert_params, $email_template->subject);
				$body    = str_replace($email_params, $alert_params, $email_template->content);

				return self::addQueue($object->email, $subject, $body);
			}
		}

		return FALSE;
	}
}