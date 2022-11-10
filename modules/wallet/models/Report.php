<?php

namespace modules\wallet\models;

use yii\base\BaseObject;
use yii\base\InvalidArgumentException;

/**
 * Report Class
 *
 * @property double $totalTopups
 * @property double $totalWithdraws
 * @property double $totalPromo
 */
class Report extends BaseObject{

	public $wallet_id;

	/**
	 * @return void
	 */
	public function init(){
		if (empty($this->wallet_id)){
			throw new InvalidArgumentException("Wallet is invalid.");
		}

		parent::init();
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getTransactions(){
		return Transaction::find()->andWhere(['wallet_id' => $this->wallet_id]);
	}

	private $_total_topups = NULL;

	/**
	 * @return float
	 */
	public function getTotalTopups(){
		if ($this->_total_topups === NULL){
			$this->_total_topups = $this->getTransactions()
			                            ->andWhere(['status' => Transaction::STATUS_SUCCESS])
			                            ->andWhere(['type' => Transaction::TYPE_TOPUP])
			                            ->sum('amount') ?? 0;

			$this->_total_topups = doubleval($this->_total_topups);
		}

		return $this->_total_topups;
	}

	private $_total_withdraws = NULL;

	/**
	 * @return float
	 */
	public function getTotalWithdraws(){
		if ($this->_total_withdraws === NULL){
			$this->_total_withdraws = $this->getTransactions()
			                               ->andWhere(['status' => Transaction::STATUS_SUCCESS])
			                               ->andWhere(['type' => Transaction::TYPE_WITHDRAW])
			                               ->sum('amount') ?? 0;

			$this->_total_withdraws = doubleval($this->_total_withdraws);
		}

		return $this->_total_withdraws;
	}

	private $_total_promo = NULL;

	/**
	 * @return float
	 */
	public function getTotalPromo(){
		if ($this->_total_promo === NULL){
			$this->_total_promo = $this->getTransactions()
			                           ->andWhere(['status' => Transaction::STATUS_SUCCESS])
			                           ->andWhere(['type' => Transaction::TYPE_REWARD])
			                           ->sum('amount') ?? 0;

			$this->_total_promo = doubleval($this->_total_promo);
		}

		return $this->_total_promo;
	}

}