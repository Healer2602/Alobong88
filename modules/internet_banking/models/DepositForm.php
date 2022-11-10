<?php

namespace modules\internet_banking\models;

use common\base\AppHelper;
use common\base\Status;
use modules\customer\frontend\models\CustomerIdentity;
use modules\internet_banking\gateway\Api;
use modules\wallet\gateways\DepositAbstract;
use Yii;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * Deposit Form
 *
 * @property-read Bank[] $banks
 * @property-read array $bankList
 * @property-read Bank $bank
 * @property-read array $accounts
 * @property-read array $channels
 */
class DepositForm extends DepositAbstract{

	public $bank_id;
	public $deposit_channel;
	public $reference_id;
	public $receipt;
	public $bank_account;
	public $account_number;
	public $account_branch;

	/**
	 * @return void
	 */
	public function init(){
		parent::init();

		if (empty($this->bank_id)){
			$this->bank_id = key($this->banks);
			$bank          = $this->banks[$this->bank_id] ?? NULL;
			if (!empty($bank->content)){
				$this->bank_account   = $bank->content['account_name'] ?? '';
				$this->account_number = $bank->content['account_id'] ?? '';
				$this->account_branch = $bank->content['account_branch'] ?? '';
			}
		}
	}

	/**
	 * @return array
	 */
	public function rules(){
		$rules = [
			[['bank_id', 'deposit_channel'], 'required'],
			['bank_id', 'integer'],
			['bank_id', 'exist', 'targetClass' => Bank::class, 'targetAttribute' => 'id'],
			[['deposit_channel', 'reference_id'], 'string'],
			['receipt', 'safe'],
			['receipt', 'validateImage']
		];

		return ArrayHelper::merge(parent::rules(), $rules);
	}

	/**
	 * @param $attribute
	 *
	 * @return void
	 */
	public function validateImage($attribute){
		if ($data = $this->$attribute){
			if (preg_match('/^data:image\/(\w+);base64,/', $this->$attribute, $type)){
				$data = substr($data, strpos($data, ',') + 1);
				$type = strtolower($type[1]);

				if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])){
					$this->addError($attribute,
						Yii::t('internet_banking', 'Invalid receipt format.'));
				}
			}
		}
	}

	/**
	 * @return array
	 */
	public function attributeLabels(){
		$labels = [
			'bank_id'         => Yii::t('internet_banking', 'Support Bank List'),
			'deposit_channel' => Yii::t('internet_banking', 'Deposit Channel'),
			'reference_id'    => Yii::t('internet_banking', 'Reference ID'),
			'bank_account'    => Yii::t('internet_banking', 'Bank Account'),
			'account_number'  => Yii::t('internet_banking', 'Account Number'),
			'receipt'         => Yii::t('internet_banking', 'Receipt'),
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

			$this->_banks = Bank::find()
			                    ->alias('ebank')
			                    ->joinWith('bank', FALSE)
			                    ->select(['ebank.*', 'name' => new Expression("IF(ebank.name IS NULL OR ebank.name = '', bank.name, ebank.name)")])
			                    ->addSelect(['logo' => new Expression("IF(ebank.logo IS NULL OR ebank.logo = '', bank.logo, ebank.logo)")])
			                    ->andWhere(['ebank.status' => Status::STATUS_ACTIVE, 'bank.status' => Status::STATUS_ACTIVE])
			                    ->andWhere(['ebank.currency_code' => $currency])
			                    ->orderBy(['bank.name' => SORT_ASC])
			                    ->indexBy('id')
			                    ->all();
		}

		return $this->_banks;
	}

	/**
	 * @return \modules\internet_banking\models\Bank|null
	 */
	public function getBank(){
		return Bank::findOne($this->bank_id);
	}

	/**
	 * @return array
	 */
	public function getAccounts(){
		return ArrayHelper::getColumn($this->banks, 'content');
	}

	/**
	 * @return array
	 */
	public function getChannels()
	: array{
		return [
			'online_banking' => Yii::t('internet_banking', 'Online Banking'),
			'bank_counter'   => Yii::t('internet_banking', 'Bank Counter'),
			'atm'            => Yii::t('internet_banking', 'ATM'),
			'otc'            => Yii::t('internet_banking', 'Over The Counter (OTC)'),
		];
	}

	/**
	 * @return bool
	 * @throws \Exception
	 */
	public function submit(){
		if ($this->validate()){
			$transaction = $this->transaction;

			if (!empty($transaction->gateway)){
				$params = $transaction->formatParams();

				$params[$this->getAttributeLabel('deposit_channel')] = $this->channels[$this->deposit_channel] ?? NULL;
				$params[$this->getAttributeLabel('reference_id')]    = $this->reference_id;
				if ($image = $this->upload()){
					$params[$this->getAttributeLabel('receipt')] = [
						'format'  => 'image',
						'content' => $image
					];
				}

				$transaction->params = $params;

				return (new Api())->deposit($this->amount, $this->bank->currency_code,
					$this->bank->code, $transaction);
			}
		}

		return FALSE;
	}

	/**
	 * @return false|string|null
	 * @throws \Exception
	 */
	public function upload(){
		if ($data = $this->receipt){
			if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)){
				$data = substr($data, strpos($data, ',') + 1);
				$type = strtolower($type[1]);

				if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])){
					return FALSE;
				}

				$data      = str_replace(' ', '+', $data);
				$file_name = 'deposit-' . time() . ".{$type}";

				return AppHelper::uploadEncodedImage($data, 'internet_banking', $file_name);
			}
		}

		return FALSE;
	}
}