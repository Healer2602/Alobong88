<?php

namespace modules\promotion\console;

use Exception;
use modules\matrix\base\Wallet as WalletAPI;
use modules\promotion\models\PromotionJoining;
use modules\wallet\models\Transaction;
use modules\wallet\models\WalletSub;
use modules\wallet\models\WalletSubTransaction;
use Throwable;
use Yii;
use yii\base\BaseObject;
use yii\queue\RetryableJobInterface;

/**
 * Restore wallet job
 */
class CancelPromoJob extends BaseObject implements RetryableJobInterface{

	/**
	 * @var \modules\promotion\models\PromotionJoining
	 */
	public $join;

	public $status = NULL;

	/**
	 * @inheritDoc
	 */
	public function execute($queue){
		$join = $this->join;
		if ($join->status == PromotionJoining::STATUS_RUNNING){
			$lost = $join->bonus + $join->totalWin;

			$join->status = $this->status ?? PromotionJoining::STATUS_FAILED;
			$transaction  = Yii::$app->db->beginTransaction();
			try{
				if ($join->save()){
					if ($this->withdraw($join->wallet, $lost)){
						$join->wallet->status = WalletSub::STATUS_ACTIVE;

						if ($join->wallet->save()){
							$transaction->commit();

							return TRUE;
						}
					}
				}
			}catch (Exception|Throwable $exception){
				$transaction->rollBack();
			}

			throw new Exception("Cannot cancel or set promotion of player to failed");
		}
	}


	/**
	 * @param \modules\wallet\models\WalletSub $subwallet
	 * @param float $withdraw_balance
	 *
	 * @return bool
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\httpclient\Exception
	 * @throws \Exception
	 */
	private function withdraw(WalletSub $subwallet, $withdraw_balance = 0){
		$current_balance = WalletAPI::balance([
			'player_id'    => $subwallet->customer->username ?? NULL,
			'product_code' => $subwallet->product_code
		]);

		if (is_null($current_balance)){
			return FALSE;
		}

		if (empty($current_balance)){
			return TRUE;
		}

		$old_balance        = $subwallet->balance;
		$subwallet->balance -= $withdraw_balance;

		$balance = floor(min($withdraw_balance, $current_balance));

		if ($subwallet->save()){
			$transaction = WalletSubTransaction::store([
				'wallet_sub_id' => $subwallet->id,
				'amount'        => $withdraw_balance,
				'balance'       => $old_balance,
				'type'          => Transaction::TYPE_TRANSFER,
				'status'        => Transaction::STATUS_SUCCESS
			]);

			if (empty($transaction)){
				throw new Exception("Invalid transaction");
			}

			$withdraw = WalletAPI::transfer([
				'player_id'      => $subwallet->customer->username ?? NULL,
				'product_code'   => $subwallet->product_code,
				'transaction_id' => $transaction->transaction_id,
				'amount'         => $balance * - 1
			]);

			if (empty($withdraw)){
				throw new Exception("Cannot withdraw from IM");
			}

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @inheritDoc
	 */
	public function getTtr(){
		return 5 * 60;
	}

	/**
	 * @inheritDoc
	 */
	public function canRetry($attempt, $error){
		return $attempt < 10;
	}
}