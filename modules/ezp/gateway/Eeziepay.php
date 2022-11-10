<?php

namespace modules\ezp\gateway;

use Exception;
use modules\wallet\frontend\models\Deposit;
use modules\wallet\gateways\GatewayAbstract;
use modules\wallet\models\Transaction;
use Throwable;
use Yii;
use yii\base\InvalidArgumentException;

/**
 * Class CoinBase
 *
 * @package modules\wallet\gateways\coinbase
 *
 * @property-read \modules\ezp\gateway\Api $api
 */
class Eeziepay extends GatewayAbstract{

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
	 * @return \modules\ezp\gateway\Api
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
			if ($type == self::TYPE_TOPUP){
				$bill = $this->topupCallback($requested_data);
			}else{
				$bill = $this->withdrawCallback($requested_data);
			}

			if (!empty($bill)){
				return <<< XML
<xml>
	<billno>{$bill}</billno>
	<status>OK</status>
</xml>
XML;
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
			'service_version' => $requested_data['service_version'] ?? NULL,
			'billno'          => $requested_data['billno'] ?? NULL,
			'partner_orderid' => $requested_data['partner_orderid'] ?? NULL,
			'currency'        => $requested_data['currency'] ?? NULL,
			'request_amount'  => $requested_data['request_amount'] ?? NULL,
			'receive_amount'  => $requested_data['receive_amount'] ?? NULL,
			'fee'             => $requested_data['fee'] ?? NULL,
			'status'          => $requested_data['status'] ?? NULL,
		];

		$signature   = $requested_data['sign'] ?? NULL;
		$is_verified = $this->api->validateHook($sign_data, $signature);
		if ($is_verified && $this->update($sign_data)){
			return $sign_data['billno'] ?? NULL;
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
			'service_version' => $requested_data['service_version'] ?? NULL,
			'billno'          => $requested_data['billno'] ?? NULL,
			'partner_orderid' => $requested_data['partner_orderid'] ?? NULL,
			'currency'        => $requested_data['currency'] ?? NULL,
			'account_name'    => $requested_data['account_name'] ?? NULL,
			'account_number'  => $requested_data['account_number'] ?? NULL,
			'bank_code'       => $requested_data['bank_code'] ?? NULL,
			'bank_charge'     => $requested_data['bank_charge'] ?? NULL,
			'fee'             => $requested_data['fee'] ?? NULL,
			'status'          => $requested_data['status'] ?? NULL,
		];

		$signature   = $requested_data['sign'] ?? NULL;
		$is_verified = $this->api->validateHook($sign_data, $signature);
		if ($is_verified){
			$bill = $sign_data['billno'] ?? NULL;
		}

		if (!empty($bill)){
			if (isset($requested_data['error_code'])){
				$sign_data['error_code'] = $requested_data['error_code'];
			}

			if (isset($requested_data['error_description'])){
				$sign_data['error_description'] = $requested_data['error_description'];
			}

			$is_verified = $this->api->validateHook($sign_data, $signature);
			if ($is_verified){
				$bill = $sign_data['billno'] ?? NULL;
			}
		}

		if (!empty($bill)){
			$transaction = Transaction::findOne([
				'transaction_id' => $requested_data['partner_orderid'] ?? NULL,
				'type'           => Transaction::TYPE_WITHDRAW,
			]);

			if (!empty($transaction)){
				$txn_params                         = $transaction->formatParams();
				$txn_params['eeziepay']['callback'] = Yii::t('wallet',
					'Callback-ed with status: {0}',
					[$this->api->getWithdrawStatus($requested_data['status'])]);
				$transaction->params                = $txn_params;
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
			'transaction_id' => $posted_data['partner_orderid'] ?? NULL,
			'status'         => Transaction::STATUS_PENDING,
		]);

		if (empty($transaction)){
			return FALSE;
		}

		if (!empty($posted_data['billno'])){
			$transaction->reference_id = $posted_data['billno'];
		}

		if ($posted_data['status'] == '002' && $posted_data['request_amount'] <= $posted_data['receive_amount']){
			$transaction->status      = Transaction::STATUS_SUCCESS;
			$transaction->description = Yii::t('wallet',
				'Eeziepay payment success: ' . $this->api->getStatus($posted_data['status']));
		}elseif ($posted_data['status'] == '001' || $posted_data['status'] == '000'){
			$transaction->description = Yii::t('wallet',
				'Eeziepay Payment updated with status: ' . $this->api->getStatus($posted_data['status']));
		}else{
			$transaction->status      = Transaction::STATUS_FAILED;
			$transaction->description = Yii::t('wallet',
				'Eeziepay Payment failed with status: ' . $this->api->getStatus($posted_data['status']));
		}

		if (!empty($posted_data['receive_amount'])){
			$params                     = $transaction->formatParams();
			$params['receive_amount']   = $posted_data['receive_amount'];
			$params['receive_currency'] = $posted_data['currency'];
			$transaction->params        = $params;
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

		return $this->api->withdraw($amount, $transaction, $params['ezp'] ?? []);
	}
}