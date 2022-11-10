<?php

namespace modules\copay\models;

use common\base\Status;
use modules\customer\frontend\models\CustomerIdentity;
use modules\customer\models\CustomerBank;
use modules\wallet\gateways\WithdrawAbstract;
use Yii;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * Withdraw Form
 *
 * @property-read Bank[] $banks
 * @property-read array $bankList
 * @property-read Bank $bank
 * @property-read array $accounts
 */
class WithdrawForm extends WithdrawAbstract{

	public $bank_id;
	public $bank_account;
	public $account_number;
	public $bank_branch;
	public $bank_province;
	public $bank_city;

	/**
	 * @return void
	 */
	public function init(){
		parent::init();

		if (empty($this->bank_id)){
			$this->bank_id = key($this->banks);
		}
	}

	/**
	 * @return array
	 */
	public function rules(){
		$rules = [
			[['bank_id'], 'required'],
			[['bank_id'], 'integer'],
			[['bank_branch', 'bank_province', 'bank_city'], 'string'],
			['bank_id', 'exist', 'targetClass' => Bank::class, 'targetAttribute' => 'id'],
		];

		return ArrayHelper::merge(parent::rules(), $rules);
	}

	/**
	 * @return array
	 */
	public function attributeLabels(){
		$labels = [
			'bank_id'        => Yii::t('copay', 'Your Bank List'),
			'bank_account'   => Yii::t('copay', 'Account Name'),
			'account_number' => Yii::t('copay', 'Account No'),
			'bank_branch'    => Yii::t('copay', 'Bank Branch'),
			'bank_province'  => Yii::t('copay', 'Bank Province'),
			'bank_city'      => Yii::t('copay', 'Bank City'),
		];

		return ArrayHelper::merge(parent::attributeLabels(), $labels);
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
			                    ->andWhere(['cbank.bank_id' => $bank_ids])
			                    ->orderBy(['bank.name' => SORT_ASC])
			                    ->indexBy('id')
			                    ->all();
		}

		return $this->_banks;
	}

	/**
	 * @return \modules\copay\models\Bank|null
	 */
	public function getBank(){
		return Bank::findOne($this->bank_id);
	}

	private $_accounts = NULL;

	/**
	 * @return array
	 */
	public function getAccounts(){
		if ($this->_accounts === NULL){
			$this->_accounts = CustomerBank::find()
			                               ->select(['account_id', 'account_name', 'account_branch', 'bank_id'])
			                               ->andWhere(['customer_id' => Yii::$app->user->getId()])
			                               ->indexBy('bank_id')
			                               ->asArray()
			                               ->all();
		}

		return $this->_accounts;
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