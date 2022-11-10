<?php

namespace modules\wallet\gateways;

use Yii;
use yii\base\Model;

/**
 * Class DepositAbstract
 *
 * @package modules\wallet\gateways
 *
 */
abstract class DepositAbstract extends Model{

	/**
	 * @var int
	 *
	 * Deposit Amount
	 */
	public $amount = 0;

	/**
	 * @var \modules\wallet\models\Transaction
	 */
	public $transaction;

	/**
	 * @return array
	 */
	public function rules(){
		return [
			['amount', 'number', 'min' => 1],
			['amount', 'required']
		];
	}

	/**
	 * @return array
	 */
	public function attributeLabels(){
		return [
			'amount' => Yii::t('wallet', 'Deposit Amount'),
		];
	}

	/**
	 * @return boolean
	 *
	 * Submit form data
	 */
	abstract public function submit();
}