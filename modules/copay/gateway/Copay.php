<?php

namespace modules\copay\gateway;

use Exception;
use modules\wallet\frontend\models\Deposit;
use modules\wallet\gateways\GatewayAbstract;
use modules\wallet\models\Transaction;
use Throwable;
use Yii;
use yii\base\InvalidArgumentException;
use yii\helpers\Json;

/**
 * Class CoinBase
 *
 * @package modules\wallet\gateways\coinbase
 *
 * @property-read \modules\copay\gateway\Api $api
 */
class Copay extends GatewayAbstract{

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
	 * @return \modules\copay\gateway\Api
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
		$requested_data = Yii::$app->request->post();
		if (!empty($requested_data)){
			if (!empty($requested_data['result']) && is_string($requested_data['result'])){
				$requested_data['raw_result'] = $requested_data['result'];
				$requested_data['result']     = Json::decode($requested_data['result']);
			}

			if ($type == self::TYPE_TOPUP){
				$bill = $this->topupCallback($requested_data);
			}else{
				$bill = $this->withdrawCallback($requested_data);
			}

			if (!empty($bill)){
				return "success";
			}
		}

		throw new InvalidArgumentException("Request is invalid.");
	}

	/**
	 * @param $requested_data
	 *
	 * @return mixed|null
	 */
	private function topupCallback($requested_data){
		$sign_data = [
			'status' => $requested_data['status'] ?? NULL,
			'result' => trim($requested_data['raw_result'] ?? ''),
		];

		$signature   = $requested_data['sign'] ?? NULL;
		$is_verified = $this->api->validateHook($sign_data, $signature);

		$sign_data['transactionid'] = $requested_data['result']['transactionid'] ?? NULL;
		$sign_data['orderid']       = $requested_data['result']['orderid'] ?? NULL;
		if ($is_verified && $this->update($sign_data)){
			return $sign_data['transactionid'] ?? NULL;
		}

		return NULL;
	}

	/**
	 * @param $requested_data
	 *
	 * @return mixed|null
	 */
	private function withdrawCallback($requested_data){
		$sign_data = [
			'status' => $requested_data['status'] ?? NULL,
			'result' => trim($requested_data['raw_result'] ?? ''),
		];

		$signature   = $requested_data['sign'] ?? NULL;
		$is_verified = $this->api->validateHook($sign_data, $signature);

		$sign_data['transactionid'] = $requested_data['result']['transactionid'] ?? NULL;
		if ($is_verified){
			$bill = $sign_data['transactionid'] ?? NULL;
		}

		if (!empty($bill)){
			$transaction = Transaction::findOne([
				'transaction_id' => $requested_data['result']['orderid'] ?? NULL,
				'type'           => Transaction::TYPE_WITHDRAW,
			]);

			if (!empty($transaction)){
				$txn_params                      = $transaction->formatParams();
				$txn_params['copay']['callback'] = Yii::t('wallet',
					'Callback-ed with status: {0}',
					[$this->api->getStatus($requested_data['status'])]);
				$transaction->params             = $txn_params;
				$transaction->save(FALSE);
			}

			return $bill;
		}

		return NULL;
	}

	/**
	 * @param array $posted_data
	 *
	 * @return bool
	 */
	private function update($posted_data){
		$transaction = Transaction::findOne([
			'transaction_id' => $posted_data['orderid'] ?? NULL,
			'status'         => Transaction::STATUS_PENDING,
		]);

		if (empty($transaction)){
			return FALSE;
		}

		if (!empty($posted_data['transactionid'])){
			$transaction->reference_id = $posted_data['transactionid'];
		}

		if ($posted_data['status'] == '10000' && !empty($posted_data['transactionid'])){
			$transaction->status      = Transaction::STATUS_SUCCESS;
			$transaction->description = Yii::t('copay',
				'Copay payment success: ' . $this->api->getStatus($posted_data['status']));
		}else{
			$transaction->status      = Transaction::STATUS_FAILED;
			$transaction->description = Yii::t('copay',
				'Copay Payment failed with status: ' . $this->api->getStatus($posted_data['status']));
		}

		$db_transaction = Yii::$app->db->beginTransaction();
		try{
			if ($transaction->save()){
				if ($transaction->status == Transaction::STATUS_SUCCESS){
					$wallet          = $transaction->wallet;
					$wallet->balance += $transaction->amount;
					if ($wallet->save(FALSE)){
						$db_transaction->commit();

						return TRUE;
					}
				}else{
					$db_transaction->commit();

					return TRUE;
				}
			}
		}catch (Exception|Throwable $exception){
			$db_transaction->rollBack();
		}

		if (!empty($exception)){
			Yii::error($exception->getMessage(), Deposit::class);
		}

		return FALSE;
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

		return $this->api->withdraw($amount, $transaction, $params['copay'] ?? []);
	}
}