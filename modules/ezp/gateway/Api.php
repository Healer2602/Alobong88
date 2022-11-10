<?php

namespace modules\ezp\gateway;

use common\base\AppHelper;
use frontend\base\Rate;
use modules\ezp\models\Bank;
use modules\wallet\models\Setting;
use modules\wallet\models\Transaction;
use Yii;
use yii\base\BaseObject;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\httpclient\Client;

/**
 * Class Base
 *
 * @package frontend\coinbase
 *
 * @property-read int $timestamp
 */
class Api extends BaseObject{

	public $apiKey;
	public $apiSecret;
	public $apiUrl;
	public $apiVersion = '3.0';

	/**
	 * @param int $amount
	 * @param string $currency
	 * @param string $bank
	 * @param \modules\wallet\models\Transaction $transaction
	 *
	 * @return array|boolean
	 * @throws \yii\base\InvalidConfigException
	 */
	public function deposit($amount, $currency, $bank, $transaction){
		$data = [
			'service_version' => $this->apiVersion,
			'partner_code'    => $this->apiKey,
			'partner_orderid' => $transaction->transaction_id,
			'member_id'       => 'CUS' . $transaction->wallet->customer_id,
			'member_ip'       => AppHelper::userIP(),
			'currency'        => $currency,
			'amount'          => $amount,
			'backend_url'     => Url::to(['/wallet/default/topup-callback', 'type' => 'ezp', 'id' => $transaction->id],
				'https'),
			'redirect_url'    => Url::to(['/wallet/default/topup-return', 'type' => 'redirect', 'id' => $transaction->id],
				'https'),
			'bank_code'       => $bank,
			'trans_time'      => Yii::$app->formatter->asDatetime($transaction->created_at,
				'php:Y-m-d H:i:s'),
		];

		$sign_data        = $data;
		$sign_data['key'] = $this->apiSecret;
		$encode_data      = urldecode(http_build_query($sign_data));
		$data['sign']     = strtoupper(sha1($encode_data));

		$params                = $transaction->formatParams();
		$transaction->params   = $params;
		$transaction->currency = $currency;

		if ($transaction->save(FALSE)){
			return [
				'url'  => $this->apiUrl,
				'data' => $data
			];
		}

		return FALSE;
	}

	/**
	 * @param array $sign_data
	 * @param $signature
	 *
	 * @return bool
	 */
	public function validateHook(array $sign_data, $signature){
		$sign_data['key'] = $this->apiSecret;
		$encode_data      = urldecode(http_build_query($sign_data));
		$signed           = strtoupper(sha1($encode_data));

		return !empty($signature) && $signed == $signature;
	}

	/**
	 * @param $status
	 *
	 * @return string
	 */
	public function getStatus($status){
		$statuses = [
			'000' => 'Success',
			'001' => 'Pending',
			'002' => 'Bank Success',
			'110' => 'Expired',
			'111' => 'Fail',
			'112' => 'Login Error',
			'113' => 'Amount Error',
			'114' => 'Pin Error',
			'115' => 'Pin Timeout',
			'116' => 'Login Timeout',
			'117' => 'Account Timeout',
			'118' => 'Security Question error',
			'119' => 'User Abort',
			'200' => 'Refunded',
		];

		return $statuses[$status] ?? 'Unknown Status';
	}

	## WITHDRAW

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
			'service_version' => $this->apiVersion,
			'partner_code'    => $this->apiKey,
			'partner_orderid' => $transaction->transaction_id,
			'member_id'       => 'CUS' . $transaction->wallet->customer_id,
			'currency'        => $transaction->currency,
			'amount'          => abs($amount) * Rate::rate($transaction->currency),
			'account_name'    => $params['account_name'] ?? NULL,
			'account_number'  => $params['account_number'] ?? NULL,
			'bank_province'   => $params['bank_province'] ?? NULL,
			'bank_city'       => $params['bank_city'] ?? NULL,
			'bank_branch'     => $params['bank_branch'] ?? NULL,
			'bank_code'       => $bank->code,
			'notify_url'      => $params['notify_url'] ?? Url::to(['/wallet/default/withdraw-callback', 'type' => 'ezp', 'id' => $transaction->id],
					'https'),
		];

		$sign_data        = $data;
		$sign_data['key'] = $this->apiSecret;
		$encode_data      = urldecode(http_build_query($sign_data));
		$data['sign']     = strtoupper(sha1($encode_data));

		if (!empty($operator)){
			$response = $this->ezpWithdraw($data);
			if (isset($response['status']) && ($response['status'] == '000' || $response['status'] == '001')){
				return TRUE;
			}

			return FALSE;
		}

		$tnx_params = $transaction->formatParams();

		$tnx_params['ezp']               = $params;
		$tnx_params['ezp']['notify_url'] = $data['notify_url'];

		$bank_name = $bank->name;
		if (empty($bank_name)){
			$bank_name = $bank->bank->name ?? NULL;
		}

		$tnx_params['Bank']              = $bank_name;
		$tnx_params['Bank Account ID']   = $params['account_number'] ?? '';
		$tnx_params['Bank Account Name'] = $params['bank_account'] ?? '';
		$tnx_params['Bank Branch']       = $params['bank_branch'] ?? '';
		$tnx_params['Bank Province']     = $params['bank_province'] ?? '';
		$tnx_params['Bank City']         = $params['bank_city'] ?? '';
		$tnx_params['Address']           = "{$bank_name} ({$params['account_number']})";
		$tnx_params['Amount']            = abs($amount) * Rate::rate($transaction->currency);
		$tnx_params['Currency']          = $transaction->wallet->customer->currency ?? '';

		$transaction->params = $tnx_params;

		if ($transaction->save(FALSE)){
			$setting = new Setting();
			$setting->getValues();

			if (!empty($setting->maximum_withdraw) && abs($transaction->amount) <= $setting->maximum_withdraw){
				$response   = $this->ezpWithdraw($data);
				$tnx_params = $transaction->formatParams();
				if (isset($response['error_code'])){
					$transaction->description        = $response['error_description'] ?? 'Unknown error.';
					$tnx_params['ezp']['error_code'] = $response['error_code'];
				}elseif (!empty($response['billno']) && isset($response['status'])){
					if ($response['status'] == '000' || $response['status'] == '001'){
						$transaction->status         = Transaction::STATUS_SUCCESS;
						$transaction->reference_id   = $response['billno'];
						$transaction->description    = Yii::t('ezp',
							'Eeziepay Payout success with status: {0}',
							[$this->getWithdrawStatus($response['status'])]);
						$tnx_params['ezp']['status'] = $response['status'];
					}else{
						$transaction->status         = Transaction::STATUS_FAILED;
						$transaction->description    = Yii::t('ezp',
							'Eeziepay Payout failed with status: {0}',
							[$this->getWithdrawStatus($response['status'])]);
						$tnx_params['ezp']['status'] = $response['status'];
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

	/**
	 * @param $data
	 *
	 * @return mixed|null
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\httpclient\Exception
	 */
	private function ezpWithdraw($data){
		$client = new Client([
			'responseConfig' => [
				'format' => Client::FORMAT_XML
			]
		]);

		$response = $client->createRequest()
		                   ->setMethod('POST')
		                   ->setFullUrl($this->apiUrl)
		                   ->addHeaders(['content-type' => 'application/x-www-form-urlencoded'])
		                   ->setData($data)
		                   ->send();

		if ($response->isOk){
			$response = $response->content;
			if (!empty($response)){
				$content = simplexml_load_string($response);
				if (!empty($content)){
					return Json::decode(Json::encode($content));
				}
			}
		}

		return NULL;
	}

	/**
	 * @param $status
	 *
	 * @return string
	 */
	public function getWithdrawStatus($status){
		$statuses = [
			'000' => 'Success',
			'111' => 'Fail',
			'001' => 'Pending',
			'112' => 'Rejected',
		];

		return $statuses[$status] ?? $status;
	}
}
