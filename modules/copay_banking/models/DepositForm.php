<?php

namespace modules\copay_banking\models;

use common\base\Status;
use modules\copay\models\Bank;
use modules\copay_banking\gateway\Api;
use modules\customer\frontend\models\CustomerIdentity;
use Yii;
use yii\db\Expression;

/**
 * Deposit Form
 *
 * @property-read Bank[] $banks
 * @property-read array $walletType
 */
class DepositForm extends \modules\copay\models\DepositForm{

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
			                    ->andWhere(['not in', 'cbank.code', $this->walletType])
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
	public function getWalletType()
	: array{
		return Api::electronicWalletType();
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