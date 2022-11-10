<?php

namespace modules\copay_banking\gateway;

/**
 * Class CoinBase
 *
 * @package modules\wallet\gateways\coinbase
 *
 * @property-read \modules\copay_banking\gateway\Api $api
 */
class Copay extends \modules\copay\gateway\Copay{

	/**
	 * @return \modules\copay_banking\gateway\Api
	 */
	public function getApi(){
		return new Api([
			'apiKey'    => $this->config['api_key'] ?? NULL,
			'apiSecret' => $this->config['api_secret'] ?? NULL,
			'apiUrl'    => $this->config['endpoint'] ?? NULL,
		]);
	}

	/**
	 * @param $amount
	 * @param $currency
	 * @param $transaction
	 * @param $operator
	 *
	 * @return bool|int
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\httpclient\Exception
	 */
	public function withdraw($amount, $currency, $transaction, $operator = FALSE){
		$params = $transaction->formatParams();

		return $this->api->withdraw($amount, $transaction, $params['copay_banking'] ?? []);
	}
}