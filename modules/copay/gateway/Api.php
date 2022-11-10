<?php

namespace modules\copay\gateway;

use common\base\AppHelper;
use frontend\base\Rate;
use modules\copay\models\Bank;
use modules\wallet\models\Setting;
use modules\wallet\models\Transaction;
use Yii;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
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

	const VNBANK_PAYMENT = 712;

	const ONLINE_BANKING = 907;

	const BANK_TRANSFER = 908;

	const MOMO_PAYMENT = 923;

	const ZALO_PAYMENT = 921;

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
		$channel = $this->getChannel($bank);
		$data    = [
			'uid'        => $this->apiKey,
			'orderid'    => $transaction->transaction_id,
			'channel'    => $channel,
			'notify_url' => Url::to(['/wallet/default/topup-callback', 'type' => 'copay', 'id' => $transaction->id],
				'https'),
			'return_url' => Url::to(['/wallet/default/topup-return', 'type' => 'redirect', 'id' => $transaction->id],
				'https'),
			'amount'     => $amount,
			'userip'     => AppHelper::userIP(),
			'user_name'  => $transaction->wallet->customer->username ?? NULL,
			'custom'     => 'CUS' . $transaction->wallet->customer_id,
			'timestamp'  => $transaction->created_at,
		];

		if ($channel == self::ONLINE_BANKING){
			$data['bank_id']    = $bank;
			$data['bank_match'] = 0;//0: inter-bank, 1: peer-to-peer
		}

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
	 * @param array $sign_data
	 * @param $signature
	 *
	 * @return bool
	 */
	public function validateHook(array $sign_data, $signature){
		ksort($sign_data);
		$sign_data['key'] = $this->apiSecret;
		$encode_data      = urldecode(http_build_query($sign_data));
		$signed           = strtoupper(md5($encode_data));

		return !empty($signature) && $signed == $signature;
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

		$channel = $this->getChannel($bank->code);
		$data    = [
			'uid'        => $this->apiKey,
			'orderid'    => $transaction->transaction_id,
			'channel'    => $channel,
			'amount'     => abs($amount) * Rate::rate($transaction->currency),
			'userip'     => AppHelper::userIP(),
			'custom'     => 'CUS' . $transaction->wallet->customer_id,
			'timestamp'  => $transaction->created_at,
			'notify_url' => $params['notify_url'] ?? Url::to(['/wallet/default/withdraw-callback', 'type' => 'copay', 'id' => $transaction->id],
					TRUE),
		];

		if ($channel == self::ONLINE_BANKING){
			$data['bank_id']    = $bank->code;
			$data['bank_match'] = 0;//0: inter-bank, 1: peer-to-peer
		}

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

		$tnx_params['copay']               = $params;
		$tnx_params['copay']['notify_url'] = $data['notify_url'];

		$bank_name = $bank->name;
		if (empty($bank_name)){
			$bank_name = $bank->bank->name ?? NULL;
		}

		$tnx_params['Bank']              = $bank_name;
		$tnx_params['Channel']           = $channel ?? '';
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
					$transaction->description          = $this->getStatus($response['status']);
					$tnx_params['copay']['error_code'] = $response['status'];
				}elseif (!empty($response['result']) && isset($response['status'])){
					if ($response['status'] == '10000' && !empty($response['result']['transactionid'])){
						$transaction->status           = Transaction::STATUS_SUCCESS;
						$transaction->reference_id     = $response['result']['transactionid'];
						$transaction->description      = Yii::t('copay',
							'Copay Payout success with status: {0}',
							[$this->getStatus($response['status'])]);
						$tnx_params['copay']['status'] = $response['status'];
					}else{
						$transaction->status           = Transaction::STATUS_FAILED;
						$transaction->description      = Yii::t('copay',
							'Copay Payout failed with status: {0}',
							[$this->getStatus($response['status'])]);
						$tnx_params['copay']['status'] = $response['status'];
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
	public function copayClient($data){
		$client = new Client(['responseConfig' => [
			'format' => Client::FORMAT_JSON
		],]);

		$response = $client->createRequest()
		                   ->setMethod('POST')
		                   ->setFullUrl($this->apiUrl)
		                   ->addHeaders(['content-type' => 'application/x-www-form-urlencoded'])
		                   ->setData($data)
		                   ->send();

		if ($response->isOk){
			$response = $response->content;
			if (!empty($response)){
				return Json::decode($response);
			}
		}

		return NULL;
	}

	/**
	 * @return array
	 */
	public static function electronicWalletType()
	: array{
		return [
			self::MOMO_PAYMENT,
			self::ZALO_PAYMENT,
		];
	}

	/**
	 * @param $status
	 *
	 * @return string
	 */
	public function getStatus($status){
		$statuses = [
			'10000' => 'successfully executed',
			'20001' => 'send mode error',
			'20002' => 'header error',
			'20003' => 'no parameters',
			'20004' => 'parameter exception',
			'20005' => 'no trade name',
			'20041' => 'did not get sign',
			'20042' => 'sign does not match',
			'20091' => 'currency disabled',
			'20092' => 'Permission disabled',
			'20093' => 'Merchant disabled',
			'20094' => 'Transaction type disabled',
			'20095' => 'Platform API line disabled',
			'20096' => 'Merchant API line disabled',
			'20097' => 'Merchant Single Point API Disable',
			'21011' => 'The order number of the merchant platform has not been obtained',
			'21012' => 'Merchant platform order number length is less than 1 character',
			'21013' => 'Merchant Dashboard order number is longer than 32 characters',
			'21014' => 'Merchant platform order number is duplicated',
			'21016' => 'Did not get payment type',
			'21017' => 'Payment type is not a number',
			'21018' => 'Payment type does not exist',
			'21019' => 'Payment type not enabled',
			'21020' => 'wrong payment type',
			'21021' => 'Callback link not obtained',
			'21022' => 'Callback link length is greater than 100 characters',
			'21026' => 'no jump link',
			'21027' => 'Jump links are longer than 100 characters',
			'21031' => 'No amount received',
			'21032' => 'Amount is not a number',
			'21033' => 'The amount is less than the single minimum amount',
			'21034' => 'The amount is greater than the maximum amount of a single transaction',
			'21035' => 'The amount is greater than the maximum transaction amount in a single day',
			'21036' => 'Client IP not obtained',
			'21037' => 'Client IP length is greater than 40 characters',
			'21041' => 'no timestamp',
			'21042' => 'Timestamps are not numbers',
			'21046' => 'no postscript',
			'21047' => 'Postscript length is more than 100 characters',
			'21071' => 'Start time length is greater than 19 characters',
			'21072' => 'Incorrect start time format',
			'21076' => 'Deadline length is greater than 19 characters',
			'21077' => 'Deadline format error',
			'21081' => 'Pages are not numbers',
			'21086' => 'The number of lines is not a number',
			'21087' => 'Number of rows is less than 1',
			'21088' => 'The number of rows is greater than 1000',
			'21101' => 'The payee\'s account opening name has not been obtained',
			'21102' => 'The account opening name of the payee must be less than 1 character in length',
			'21103' => 'The account opening name of the payee must be longer than 50 characters',
			'21106' => 'The recipient\'s bank account number was not obtained',
			'21107' => 'The recipient\'s bank account number is less than 1 character in length',
			'21108' => 'The recipient\'s bank account number is longer than 20 characters',
			'21111' => 'no bank number',
			'21112' => 'Bank number is not a number',
			'21113' => 'Bank number does not exist',
			'21114' => 'Bank ID not enabled',
			'21115' => 'wrong bank number',
			'21116' => 'Did not get the account opening branch',
			'21117' => 'Account branch length is less than 1 character',
			'21118' => 'Account branch length is greater than 20 characters',
			'21121' => 'The province where the bank is located has not been obtained',
			'21122' => 'The length of the province where the account bank is located is less than 1 character',
			'21123' => 'The province where the account bank is located must be longer than 20 characters',
			'21126' => 'The city where the bank is located has not been obtained',
			'21127' => 'The city where the bank is located must be less than 1 character long',
			'21128' => 'The city where the bank is located must be longer than 20 characters',
			'21136' => 'IP not allowed',
			'21137' => 'Unallowed proxy payment IP',
			'21141' => 'Did not get IFSC',
			'21142' => 'IFSC length is greater than 20 characters',
			'30001' => 'Merchant does not exist',
			'30002' => 'The merchant has not opened this transaction channel',
			'30003' => 'Merchant has not set this transaction channel',
			'30004' => 'Merchant has not opened this transaction type',
			'30011' => 'Transaction order creation failed',
			'30016' => 'Transaction ticket does not exist',
			'30020' => 'Insufficient merchant balance',
			'30021' => 'The platform API does not support this currency',
			'30100' => 'payment channel blocking',
			'30901' => 'Order expired',
			'30906' => 'Login failed',
			'30907' => 'Insufficient balance',
			'30911' => 'verification failed',
			'30912' => 'Real name failed',
			'30916' => 'transaction failed',
			'30921' => 'Transaction timed out',
			'90001' => 'In maintenance',
			'90091' => 'Too many API IP jumps',
			'90092' => 'API IP is in blacklist',
			'90093' => 'No cashier mode is provided',
			'90901' => 'The wrong format returned by the gold flow channel',
			'90902' => 'The cash flow channel does not return results',
			'90903' => 'Cash flow channel return failed',
			'90904' => 'There is no return link in the cash flow channel',
			'90905' => 'The return link of the gold flow channel is empty',
			'90906' => 'Gold flow channel transaction failed',
			'99999' => 'without this API',
		];

		return $statuses[$status] ?? 'Unknown Status';
	}

	/**
	 * @param $bank_code
	 *
	 * @return int
	 */
	private function getChannel($bank_code){
		if (ArrayHelper::isIn($bank_code, self::electronicWalletType())){
			return (int) $bank_code;
		}

		return self::ONLINE_BANKING;
	}
}