<?php

namespace modules\notification;

use modules\notification\models\TelegramSetting;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidArgumentException;
use yii\httpclient\Client;
use yii\queue\RetryableJobInterface;

/**
 * Class TelegramJob
 *
 * @package modules\notification
 */
class TelegramJob extends BaseObject implements RetryableJobInterface{

	public $message;
	public $url;

	/**
	 * @param EmailJob $queue which pushed and is handling the job
	 *
	 * @return bool|void result of the job execution
	 * @throws \Exception
	 */
	public function execute($queue){
		if (empty($this->url)){
			$setting = new TelegramSetting();
			$setting->getValues();

			$endpoint = $setting->telegram_url;
		}else{
			$endpoint = $this->url;
		}

		if (!empty($endpoint)){
			$urls = parse_url($endpoint);
			parse_str($urls['query'], $vars);

			$vars['parse_mode'] = 'HTML';
			$vars['text']       = $this->message;
			$query              = http_build_query($vars);
			$my_endpoint        = $urls['scheme'] . '://' . $urls['host'] . $urls['path'] . '?' . $query;

			if ($this->send($my_endpoint)){
				return TRUE;
			}

			throw new InvalidArgumentException('Cannot notify');
		}
	}

	/**
	 * @param $endpoint
	 *
	 * @return bool
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\httpclient\Exception
	 */
	private function send($endpoint){
		$client   = new Client([
			'transport' => 'yii\httpclient\CurlTransport'
		]);
		$response = $client->createRequest()
		                   ->setMethod('GET')
		                   ->setUrl($endpoint)
		                   ->setOptions([
			                   CURLOPT_SSL_VERIFYHOST => 0, // connection timeout
			                   CURLOPT_SSL_VERIFYPEER => 0, // connection timeout
		                   ])
		                   ->send();

		if ($response->isOk){
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return int time to reserve in seconds
	 */
	public function getTtr(){
		return 3 * 60;
	}

	/**
	 * @param int $attempt number
	 * @param \Exception|\Throwable $error from last execute of the job
	 *
	 * @return bool
	 */
	public function canRetry($attempt, $error){
		Yii::error($error, self::class);

		return $attempt < 11;
	}
}