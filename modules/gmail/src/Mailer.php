<?php

namespace modules\gmail\src;

use Google\Service\Gmail;
use yii\mail\BaseMailer;

/**
 * Google Mailer
 */
class Mailer extends BaseMailer{

	/**
	 * @var string
	 */
	public $messageClass = Message::class;

	/**
	 * @param \yii\mail\BaseMessage $message
	 *
	 * @return bool
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\db\Exception
	 */
	protected function sendMessage($message){
		if ($client = $this->getClient()){
			$model = new \Google\Service\Gmail\Message();
			$model->setRaw($message->toString());

			$service  = new Gmail($client);
			$response = $service->users_messages->send('me', $model);

			if ($response->getId()){
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * @var null|\Google_Client
	 */
	private $_client = NULL;

	/**
	 * @return array|\Google_Client
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\db\Exception
	 */
	protected function getClient(){
		if ($this->_client === NULL){
			$api           = new GmailAPI();
			$this->_client = $api->getClient() ?: [];
		}

		return $this->_client;
	}
}