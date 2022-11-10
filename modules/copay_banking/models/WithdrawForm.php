<?php

namespace modules\copay_banking\models;

use common\base\Status;
use modules\copay\models\Bank;
use modules\copay_banking\gateway\Api;
use modules\customer\frontend\models\CustomerIdentity;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * Withdraw Form
 *
 * @property-read Bank[] $banks
 * @property-read array $walletType
 *
 */
class WithdrawForm extends \modules\copay\models\WithdrawForm{

	/**
	 * @return array
	 */
	public function rules(){
		$rules = [
			[['bank_id'], 'required'],
			[['bank_id'], 'integer'],
			[['bank_branch', 'bank_province', 'bank_city'], 'string'],
			['bank_id', 'exist', 'targetClass' => Bank::class, 'targetAttribute' => 'id'],
			[['bank_account', 'account_number', 'bank_branch'], 'required']
		];

		return ArrayHelper::merge(parent::rules(), $rules);
	}

	private $_banks = NULL;

	/**
	 * @return Bank[]
	 */
	public function getBanks(){
		if ($this->_banks === NULL){
			$currency = CustomerIdentity::profile()->currency ?? NULL;
			$bank_ids = CustomerIdentity::profile()->bankIds ?? NULL;

			$this->_banks = Bank::find()
			                    ->alias('cbank')
			                    ->joinWith('bank', FALSE)
			                    ->select(['cbank.*', 'name' => new Expression("IF(cbank.name IS NULL OR cbank.name = '', bank.name, cbank.name)")])
			                    ->addSelect(['logo' => new Expression("IF(cbank.logo IS NULL OR cbank.logo = '', bank.logo, cbank.logo)")])
			                    ->andWhere(['cbank.status' => Status::STATUS_ACTIVE, 'bank.status' => Status::STATUS_ACTIVE])
			                    ->andWhere(['cbank.currency_code' => $currency])
			                    ->andWhere(['cbank.visibility' => [Bank::VISIBILITY_ALL, Bank::VISIBILITY_WITHDRAW]])
			                    ->andWhere(['not in', 'cbank.code', $this->walletType])
			                    ->andWhere(['cbank.bank_id' => $bank_ids])
			                    ->orderBy(['bank.name' => SORT_ASC])
			                    ->indexBy('id')
			                    ->all();
		}

		return $this->_banks;
	}

	/**
	 * @return array
	 */
	public function getWalletType()
	: array{
		return Api::electronicWalletType();
	}

	/**
	 * @return bool
	 * @throws \Exception
	 */
	public function submit(){
		if ($this->validate()){
			$transaction = $this->transaction;
			if (!empty($transaction->gateway)){
				return $transaction->gateway->gateway->withdraw($this->amount,
					$transaction->currency, $transaction);
			}
		}

		return FALSE;
	}
}