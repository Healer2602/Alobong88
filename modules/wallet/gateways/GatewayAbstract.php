<?php

namespace modules\wallet\gateways;

use modules\wallet\models\Transaction;
use yii\base\BaseObject;

/**
 * Class SourceAbstract
 *
 * @package modules\wallet\gateways
 *
 * @property-read array $supportCoins
 *
 * @method returnOrder(Transaction $transaction)
 */
abstract class GatewayAbstract extends BaseObject{

	const TYPE_TOPUP = 'topup';

	const TYPE_WITHDRAW = 'withdraw';

	public $config;

	/**
	 * @return array
	 * format:
	 * [
	 *      ['id' => $item1, 'name' => $item1],
	 *      ['id' => $item2, 'name' => $item2]
	 * ];
	 */
	abstract public function getSupportCoins();

	/**a
	 *
	 * @param string $type
	 *
	 * @return boolean
	 */
	abstract public function IPN($type = self::TYPE_TOPUP);

	/**
	 * @param float $amount
	 * @param string $currency
	 * @param \modules\wallet\models\Transaction $transaction
	 * @param bool $operator
	 *
	 * @return bool
	 */
	abstract public function withdraw($amount, $currency, $transaction, $operator = FALSE);
}