<?php

namespace modules\matrix\base;

use common\models\AuditTrail;
use modules\matrix\models\Setting;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidArgumentException;
use yii\helpers\Json;
use yii\httpclient\Client;
use const CURLOPT_CONNECTTIMEOUT;
use const CURLOPT_TIMEOUT;

/**
 * API Helper
 */
class API extends BaseObject{

	/**
	 * @var null
	 */
	private static $_settings = NULL;

	/**
	 * @return Setting
	 */
	public static function settings(){
		if (self::$_settings === NULL){
			$setting = new Setting();
			$setting->getValues();

			self::$_settings = $setting;
		}

		return self::$_settings;
	}

	/**
	 * @param $func
	 * @param $data
	 * @param string $method
	 *
	 * @return string|array|null
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\httpclient\Exception
	 */
	private static function request($func, $data, $method = 'POST'){
		$client = new Client([
			'transport' => 'yii\httpclient\CurlTransport'
		]);

		$data['MerchantCode'] = self::settings()->merchant_code;

		$response = $client->createRequest()
		                   ->setMethod($method)
		                   ->setFullUrl(self::settings()->endpoint . $func)
		                   ->setFormat(Client::FORMAT_JSON)
		                   ->setOptions([
			                   CURLOPT_CONNECTTIMEOUT => 120, // connection timeout
			                   CURLOPT_TIMEOUT        => 120, // data receiving timeout
			                   CURLOPT_SSL_VERIFYHOST => 0,
			                   CURLOPT_SSL_VERIFYPEER => 0
		                   ])
		                   ->setData($data)
		                   ->send();

		if ($response->isOk){
			$content = $response->getContent();

			if (!empty($content)){
				try{
					$response_content = Json::decode($content);

					if (isset($response_content['Code'])){
						if ($response_content['Code'] === 0 || $response_content['Code'] === '0'){
							return $response_content;
						}

						if ($response_content['Code'] === 558 || $response_content['Code'] === '558'){
							return [];
						}

						if (($response_content['Code'] === 518 || $response_content['Code'] === '518') && (strpos($func,
									'/Game/') !== FALSE)){
							return FALSE;
						}
					}

					self::log($func, $data, $content);
				}catch (InvalidArgumentException $exception){
					Yii::error($exception->getMessage(), self::class);

					return NULL;
				}
			}
		}elseif ($content = $response->getContent()){
			self::log($func, $data, $content);
		}

		return NULL;
	}

	/**
	 * @param $func
	 * @param $data
	 * @param $is_post
	 *
	 * @return string|null
	 */
	public static function aRequest($func, $data, $is_post = TRUE){
		/**@var \common\base\Queue $queue */
		$queue = Yii::$app->queue;

		return $queue->push(new ApiJob([
			'function_name' => $func,
			'data'          => $data,
			'method_post'   => $is_post
		]));
	}

	/**
	 * @param $func
	 * @param $data
	 *
	 * @return array|string|null
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\httpclient\Exception
	 */
	public static function post($func, $data){
		return self::request($func, $data);
	}

	/**
	 * @param $func
	 * @param $data
	 *
	 * @return array|string|null
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\httpclient\Exception
	 */
	public static function get($func, $data){
		return self::request($func, $data, 'GET');
	}

	/**
	 * @param $endpoint
	 * @param $request
	 * @param $response
	 *
	 * @return void
	 */
	private static function log($endpoint, $request, $response){
		if (is_array($request)){
			$request = Json::encode($request);
		}

		$body = Yii::t("common", "Request:\n{0}\n\nResponse:\n{1}", [
			$request, $response
		]);

		$module = basename(dirname($endpoint));

		AuditTrail::log($endpoint, $body, $module, NULL, AuditTrail::STATUS_FAILED,
			AuditTrail::SYSTEM_API);
	}
}