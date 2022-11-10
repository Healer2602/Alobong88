<?php

namespace modules\matrix\base;

use modules\game\models\ProductWallet;
use yii\base\BaseObject;

/**
 * Play Game
 */
class Wallet extends BaseObject{

	/**
	 * @param $data
	 *
	 * @return bool
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\httpclient\Exception
	 */
	public static function transfer($data){
		if (empty($data['player_id']) || empty($data['amount'])){
			return FALSE;
		}

		$rate   = self::findRate($data['product_code']);
		$amount = $data['amount'] * $rate;

		$body = [
			"PlayerId"      => $data['player_id'],
			"ProductWallet" => $data['product_code'],
			"TransactionId" => $data['transaction_id'],
			"Amount"        => $amount
		];

		return API::post('/Transaction/PerformTransfer', $body);
	}

	/**
	 * @param $code
	 *
	 * @return int|string
	 */
	private static function findRate($code){
		$rate = ProductWallet::find()
		                     ->select(['rate'])
		                     ->andWhere(['code' => $code])
		                     ->scalar();

		return empty($rate) ? 1 : $rate;
	}

	/**
	 * @param $data
	 *
	 * @return float|int|null
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\httpclient\Exception
	 */
	public static function balance($data){
		$body = [
			"PlayerId"      => $data['player_id'],
			"ProductWallet" => $data['product_code'],
		];

		$response = API::post('/Player/GetBalance', $body);
		if (isset($response['Balance'])){
			return $response['Balance'] / self::findRate($data['product_code']);
		}

		return NULL;
	}
}