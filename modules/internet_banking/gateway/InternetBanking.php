<?php

namespace modules\internet_banking\gateway;

use modules\wallet\gateways\GatewayAbstract;

/**
 * Class internet_banking
 *
 * @package modules\wallet\gateways\coinbase
 *
 * @property-read \modules\internet_banking\gateway\Api $api
 */
class InternetBanking extends GatewayAbstract{

	/**
	 * @return array
	 */
	public function getSupportCoins(){
		$result = [];
		foreach ($this->config['currency'] as $item){
			$result[] = ['id' => $item, 'name' => $item];
		}

		return $result;
	}

	/**
	 * @return \modules\internet_banking\gateway\Api
	 */
	public function getApi(){
		return new Api([
			'apiKey'    => $this->config['api_key'] ?? NULL,
			'apiSecret' => $this->config['api_secret'] ?? NULL,
			'apiUrl'    => $this->config['endpoint'] ?? NULL,
		]);
	}

	/**
	 * @param string $type
	 *
	 * @return bool
	 */
	public function IPN($type = self::TYPE_TOPUP){
		return TRUE;
	}

	/**
	 * @param $amount
	 * @param $currency
	 * @param $transaction
	 * @param $operator
	 *
	 * @return bool|int
	 */
	public function withdraw($amount, $currency, $transaction, $operator = FALSE){
		$params = $transaction->formatParams();

		return $this->api->withdraw($amount, $transaction, $params['internet_banking'] ?? []);
	}
}