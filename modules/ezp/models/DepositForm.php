<?php

namespace modules\ezp\models;

use common\base\Status;
use modules\customer\frontend\models\CustomerIdentity;
use modules\ezp\gateway\Api;
use modules\wallet\gateways\DepositAbstract;
use Yii;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * Deposit Form
 *
 * @property-read Bank[] $banks
 * @property-read array $bankList
 * @property-read \modules\ezp\models\Bank $bank
 */
class DepositForm extends DepositAbstract{

	public $bank_id;

	public const UST = ['USDT.ER.CT', 'USDT.BE.CT'];

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
			['bank_id', 'required'],
			['bank_id', 'integer'],
			['bank_id', 'exist', 'targetClass' => Bank::class, 'targetAttribute' => 'id'],
		];

		return ArrayHelper::merge(parent::rules(), $rules);
	}

	/**
	 * @return array
	 */
	public function attributeLabels(){
		$labels = [
			'bank_id' => Yii::t('ezp', 'Support Bank List'),
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
			                    ->andWhere(['visibility' => [Bank::VISIBILITY_ALL, Bank::VISIBILITY_TOPUP]])
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
	 * @return \modules\ezp\models\Bank|null
	 */
	public function getBank(){
		return Bank::findOne($this->bank_id);
	}

	/**
	 * @return bool
	 * @throws \yii\base\InvalidConfigException
	 */
	public function submit(){
		if ($this->validate()){
			if (!empty($this->transaction->gateway)){
				$api = new Api([
					'apiKey'    => $this->transaction->gateway->api_key,
					'apiSecret' => $this->transaction->gateway->api_secret,
					'apiUrl'    => $this->transaction->gateway->endpoint,
				]);

				$response = $api->deposit($this->amount * 100, $this->bank->currency_code,
					$this->bank->code,
					$this->transaction);

				if (!empty($response)){
					echo Yii::$app->view->render('@modules/ezp/gateway/deposit-continue.php', [
						'data' => $response
					]);
					exit();
				}
			}
		}

		return FALSE;
	}
}