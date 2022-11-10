<?php

namespace modules\wallet\backend\models;

use Exception;
use modules\wallet\models\Gateway;
use modules\wallet\models\Transaction;
use Throwable;
use Yii;
use yii\base\Model;

/**
 * Class UpdateTransaction
 *
 * @package modules\wallet\backend\models
 */
class UpdateTransaction extends Model{

	public $transaction_id;
	public $amount;
	public $currency;
	public $wallet_id;
	public $gateway;
	public $confirm = 0;

	/**
	 * @return array
	 */
	public function rules(){
		return [
			['confirm', 'boolean', 'trueValue' => 1, 'falseValue' => 0],
			['confirm', 'required', 'requiredValue' => 1, 'message' => Yii::t('wallet',
				'Confirm this update')],
			[['transaction_id', 'amount', 'currency', 'wallet_id', 'gateway'], 'safe']
		];
	}

	/**
	 * @return array
	 */
	public function attributeLabels(){
		return [
			'confirm'        => Yii::t('wallet', 'Confirm this update'),
			'receive_amount' => Yii::t('wallet', 'Received Amount'),
			'new_amount'     => Yii::t('wallet', 'New Amount'),
		];
	}


	/**
	 * @return string
	 */
	public function getDepositAmount(){
		return Yii::$app->formatter->asDecimal($this->amount / 100) . ' ' . $this->currency;
	}

	/**
	 * @return \modules\wallet\models\Gateway|null
	 */
	public function getGateway(){
		return Gateway::findOne(['key' => $this->gateway, 'type' => Gateway::TYPE_TOPUP]);
	}

	/**
	 * @return false|float|int
	 */
	public function getReceiveAmount(){
		$gateway = $this->getGateway();
		if (empty($gateway)){
			return 0;
		}

		$amount = 0;
		if ($this->currency == Yii::$app->formatter->currencyCode){
			$amount = $this->amount;
		}else{
			if ($this->gateway == 'ezp'){
				$rate = Currency::find()
				                ->select('rate')
				                ->andWhere(['code' => $this->currency])
				                ->scalar();

				if (!empty($rate)){
					$amount = $this->amount / 100 / $rate;
				}
			}
		}

		if (!empty($amount)){
			$topup_amount = $amount / (1 + $gateway->fee / 100);

			return floor($topup_amount);
		}

		return 0;
	}

	/**
	 * @return bool
	 */
	public function save(){
		$received = $this->getReceiveAmount();
		if (!empty($received)){
			$model = new Transaction([
				'type'         => Transaction::TYPE_TOPUP,
				'reference_id' => $this->transaction_id,
				'wallet_id'    => $this->wallet_id,
				'amount'       => $received,
				'status'       => Transaction::STATUS_SUCCESS,
				'description'  => Yii::t('wallet', 'From deposit request {0} by {1}', [
					$this->transaction_id, Yii::$app->user->identity->name ?? 'SYSTEM'
				]),
				'params'       => [
					'receive_amount'   => $this->amount,
					'receive_currency' => $this->currency,
					'payment_gateway'  => $this->gateway
				]
			]);

			$db_transaction = Yii::$app->db->beginTransaction();
			try{
				if ($model->save()){
					$wallet          = $model->wallet;
					$wallet->balance += $model->amount;
					if ($wallet->save(FALSE)){
						$db_transaction->commit();

						return TRUE;
					}
				}
			}catch (Exception $exception){
				$db_transaction->rollBack();
			}catch (Throwable $exception){
				$db_transaction->rollBack();
			}
		}

		return FALSE;
	}
}