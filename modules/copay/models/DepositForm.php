<?php

namespace modules\copay\models;

use common\base\Status;
use modules\copay\gateway\Api;
use modules\customer\frontend\models\CustomerIdentity;
use modules\wallet\gateways\DepositAbstract;
use Yii;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * Deposit Form
 *
 * @property-read Bank[] $banks
 * @property-read array $bankList
 * @property-read \modules\copay\models\Bank $bank
 */
class DepositForm extends DepositAbstract{

	public $bank_id;

	/**
	 * @return void
	 */
	public function init(){
		parent::init();

		if (empty($this->bank_id)){
			$this->bank_id = key($this->bankList);
		}
	}

	/**
	 * @return array
	 */
	public function rules(){
		$rules = [
			[['bank_id'], 'required'],
			[['bank_id'], 'integer'],
			['bank_id', 'exist', 'targetClass' => Bank::class, 'targetAttribute' => 'id'],
		];

		return ArrayHelper::merge(parent::rules(), $rules);
	}

	/**
	 * @return array
	 */
	public function attributeLabels(){
		$labels = [
			'bank_id' => Yii::t('copay', 'Support Bank List'),
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
			                    ->alias('cbank')
			                    ->joinWith('bank', FALSE)
			                    ->select(['cbank.*', 'name' => new Expression("IF(cbank.name IS NULL OR cbank.name = '', bank.name, cbank.name)")])
			                    ->addSelect(['logo' => new Expression("IF(cbank.logo IS NULL OR cbank.logo = '', bank.logo, cbank.logo)")])
			                    ->andWhere(['cbank.status' => Status::STATUS_ACTIVE, 'bank.status' => Status::STATUS_ACTIVE])
			                    ->andWhere(['cbank.currency_code' => $currency])
			                    ->andWhere(['cbank.visibility' => [Bank::VISIBILITY_ALL, Bank::VISIBILITY_TOPUP]])
			                    ->orderBy(['bank.name' => SORT_ASC])
			                    ->indexBy('id')
			                    ->asArray()
			                    ->all();
		}

		return $this->_banks;
	}

	/**
	 * @return array
	 */
	public function getBankList(){
		return ArrayHelper::map($this->banks, 'id', 'name');
	}

	/**
	 * @return \modules\copay\models\Bank|null
	 */
	public function getBank(){
		return Bank::findOne($this->bank_id);
	}

	/**
	 * @return bool
	 * @throws \yii\base\InvalidConfigException|\yii\httpclient\Exception
	 */
	public function submit(){
		if ($this->validate()){
			if (!empty($this->transaction->gateway)){
				$api = new Api([
					'apiKey'    => $this->transaction->gateway->api_key,
					'apiSecret' => $this->transaction->gateway->api_secret,
					'apiUrl'    => $this->transaction->gateway->endpoint,
				]);

				$response = $api->deposit($this->amount, $this->bank->currency_code,
					$this->bank->code,
					$this->transaction);

				if (!empty($response)){
					Yii::$app->response->redirect($response['url'])->send();
					exit();
				}
			}
		}

		return FALSE;
	}
}