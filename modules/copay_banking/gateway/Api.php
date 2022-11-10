<?php

namespace modules\copay_banking\gateway;

use common\base\AppHelper;
use frontend\base\Rate;
use modules\copay\models\Bank;
use modules\wallet\models\Setting;
use modules\wallet\models\Transaction;
use Yii;
use yii\helpers\Url;

/**
 * Class Base
 *
 * @package frontend\coinbase
 *
 * @property-read int $timestamp
 */
class Api extends \modules\copay\gateway\Api{

	/**
	 * @param int $amount
	 * @param string $currency
	 * @param string $bank
	 * @param \modules\wallet\models\Transaction $transaction
	 *
	 * @return array|boolean
	 * @throws \yii\base\InvalidConfigException|\yii\httpclient\Exception
	 */
	public function deposit($amount, $currency, $bank, $transaction){
		$channel = self::BANK_TRANSFER;
		$data    = [
			'uid'        => $this->apiKey,
			'orderid'    => $transaction->transaction_id,
			'channel'    => $channel,
			'notify_url' => Url::to(['/wallet/default/topup-callback', 'type' => 'copay_banking', 'id' => $transaction->id],
				'https'),
			'return_url' => Url::to(['/wallet/default/topup-return', 'type' => 'redirect', 'id' => $transaction->id],
				'https'),
			'amount'     => $amount,
			'userip'     => AppHelper::userIP(),
			'custom'     => 'CUS' . $transaction->wallet->customer_id,
			'timestamp'  => $transaction->created_at,
			'bank_id'    => $bank,
			'bank_match' => 0, //0: inter-bank, 1: peer-to-peer
		];

		$sign_data = $data;
		ksort($sign_data);
		$sign_data['key'] = $this->apiSecret;
		$encode_data      = urldecode(http_build_query($sign_data));
		$data['sign']     = strtoupper(md5($encode_data));

		$params                = $transaction->formatParams();
		$transaction->params   = $params;
		$transaction->currency = $currency;

		if ($transaction->save(FALSE)){
			$response = $this->copayClient($data);

			if (isset($response['status']) && ($response['status'] == '10000') && !empty($response['result']['payurl'])){
				return [
					'url' => $response['result']['payurl']
				];
			}
		}

		return FALSE;
	}

	/**
	 * @param int $amount
	 * @param \modules\wallet\models\Transaction $transaction
	 * @param array $params
	 * @param bool $operator
	 *
	 * @return boolean
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\httpclient\Exception
	 */
	public function withdraw($amount, $transaction, $params, $operator = FALSE){
		$bank = Bank::findOne($params['bank_id'] ?? NULL);
		if (empty($bank)){
			return FALSE;
		}

		$data = [
			'uid'           => $this->apiKey,
			'orderid'       => $transaction->transaction_id,
			'channel'       => self::VNBANK_PAYMENT,
			'amount'        => abs($amount) * Rate::rate($transaction->currency),
			'userip'        => AppHelper::userIP(),
			'custom'        => 'CUS' . $transaction->wallet->customer_id,
			'timestamp'     => $transaction->created_at,
			'notify_url'    => $params['notify_url'] ?? Url::to(['/wallet/default/withdraw-callback', 'type' => 'copay_banking', 'id' => $transaction->id],
					TRUE),
			'user_name'     => $transaction->wallet->customer->username,
			'bank_id'       => $bank->code,
			'bank_account'  => $params['bank_account'] ?? NULL,
			'bank_no'       => $params['account_number'] ?? NULL,
			'bank_province' => $params['bank_province'] ?? NULL,
			'bank_city'     => $params['bank_city'] ?? NULL,
			'bank_sub'      => $params['bank_branch'] ?? NULL
		];

		$sign_data = $data;
		ksort($sign_data);
		$sign_data['key'] = $this->apiSecret;
		$encode_data      = urldecode(http_build_query($sign_data));
		$data['sign']     = strtoupper(md5($encode_data));

		if (!empty($operator)){
			$response = $this->copayClient($data);

			if (isset($response['status']) && ($response['status'] == '10000')){
				return TRUE;
			}

			return FALSE;
		}

		$tnx_params = $transaction->formatParams();

		$tnx_params['copay_banking']               = $params;
		$tnx_params['copay_banking']['notify_url'] = $data['notify_url'];

		$bank_name = $bank->name;
		if (empty($bank_name)){
			$bank_name = $bank->bank->name ?? NULL;
		}

		$tnx_params['Bank']              = $bank_name;
		$tnx_params['Channel']           = self::VNBANK_PAYMENT;
		$tnx_params['Bank Account ID']   = $params['account_number'] ?? '';
		$tnx_params['Bank Account Name'] = $params['bank_account'] ?? '';
		$tnx_params['Bank Branch']       = $params['bank_branch'] ?? '';
		$tnx_params['Bank Province']     = $params['bank_province'] ?? '';
		$tnx_params['Bank City']         = $params['bank_city'] ?? '';
		$tnx_params['Address']           = "{$bank_name} ({$params['account_number']})";
		$tnx_params['Amount']            = abs($amount) * Rate::rate($transaction->currency);
		$tnx_params['Currency']          = $transaction->currency ?? '';

		$transaction->params = $tnx_params;

		if ($transaction->save(FALSE)){
			$setting = new Setting();
			$setting->getValues();

			if (!empty($setting->maximum_withdraw) && abs($transaction->amount) <= $setting->maximum_withdraw){
				$response   = $this->copayClient($data);
				$tnx_params = $transaction->formatParams();
				if (isset($response['status']) && $response['status'] != '10000'){
					$transaction->description                  = $this->getStatus($response['status']);
					$tnx_params['copay_banking']['error_code'] = $response['status'];
				}elseif (!empty($response['result']) && isset($response['status'])){
					if ($response['status'] == '10000' && !empty($response['result']['transactionid'])){
						$transaction->status                   = Transaction::STATUS_SUCCESS;
						$transaction->reference_id             = $response['result']['transactionid'];
						$transaction->description              = Yii::t('copay',
							'Copay Payout success with status: {0}',
							[$this->getStatus($response['status'])]);
						$tnx_params['copay_banking']['status'] = $response['status'];
					}else{
						$transaction->status                   = Transaction::STATUS_FAILED;
						$transaction->description              = Yii::t('copay',
							'Copay Payout failed with status: {0}',
							[$this->getStatus($response['status'])]);
						$tnx_params['copay_banking']['status'] = $response['status'];
					}
				}
				$transaction->params = $tnx_params;
				if ($transaction->save(FALSE)){
					if ($transaction->status == Transaction::STATUS_FAILED){
						return FALSE;
					}

					if ($transaction->status == Transaction::STATUS_SUCCESS){
						return TRUE;
					}
				}
			}else{
				return TRUE;
			}
		}

		return 0;
	}
}