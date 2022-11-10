<?php

namespace modules\internet_banking\gateway;

use frontend\base\Rate;
use modules\wallet\models\Bank;
use modules\wallet\models\Transaction;
use yii\base\BaseObject;

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
	public $apiVersion = '1.0';
	public $apiUrl;

	/**
	 * @param int $amount
	 * @param string $currency
	 * @param string $bank
	 * @param \modules\wallet\models\Transaction $transaction
	 *
	 * @return boolean
	 */
	public function deposit($amount, $currency, $bank, $transaction){
		$params         = $transaction->formatParams();
		$params['Bank'] = $bank;

		$transaction->params   = $params;
		$transaction->status   = Transaction::STATUS_PROCESSING;
		$transaction->currency = $currency;

		if ($transaction->save(FALSE)){
			return TRUE;
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
	 */
	public function withdraw($amount, $transaction, $params, $operator = FALSE){
		if (!empty($operator)){
			return TRUE;
		}

		$tnx_params                      = $transaction->formatParams();
		$tnx_params['Bank Account ID']   = $params['account_number'] ?? '';
		$tnx_params['Bank Account Name'] = $params['bank_account'] ?? '';
		$tnx_params['Bank Branch']       = $params['bank_branch'] ?? '';
		$tnx_params['Bank Province']     = $params['bank_province'] ?? '';

		$bank = Bank::findOne($params['bank_id'] ?? 0);
		if (!empty($bank)){
			$bank_name             = $bank->name;
			$tnx_params['Bank']    = $bank_name;
			$tnx_params['Address'] = "{$bank_name} ({$params['account_number']})";
		}else{
			$tnx_params['Address'] = $params['account_number'];
		}

		$tnx_params['Amount']   = abs($amount) * Rate::rate($transaction->currency);
		$tnx_params['Currency'] = $transaction->currency ?? '';

		$transaction->params = $tnx_params;
		$transaction->status = Transaction::STATUS_PENDING;

		if ($transaction->save(FALSE)){
			return TRUE;
		}

		return 0;
	}
}
