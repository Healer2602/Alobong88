<?php

namespace modules\wallet\console\jobs;

use Exception;
use modules\matrix\base\Wallet as WalletAPI;
use modules\wallet\models\Transaction;
use modules\wallet\models\TransferHistory;
use modules\wallet\models\Wallet;
use modules\wallet\models\WalletSub;
use modules\wallet\models\WalletSubTransaction;
use Throwable;
use Yii;
use yii\base\BaseObject;
use yii\queue\RetryableJobInterface;

/**
 * Restore wallet job
 */
class RestoreJob extends BaseObject implements RetryableJobInterface{

	public $wallet_id;

	/**
	 * @var Wallet
	 */
	private $_wallet;

	/**
	 * @inheritDoc
	 */
	public function execute($queue){
		if ($sub_wallets = $this->findSubWallets()){
			$total  = 0;
			$failed = 0;
			foreach ($sub_wallets as $sub_wallet){
				if (!empty($sub_wallet->balance)){
					try{
						$this->transfer($sub_wallet);
					}catch (Exception $exception){
						$failed ++;
					}

					$total ++;
				}
			}

			if (!empty($failed)){
				throw new Exception("Cannot withdraw all wallets");
			}
		}
	}

	/**
	 * @param \modules\wallet\models\WalletSub $subwallet
	 *
	 * @return void
	 */
	private function transfer(WalletSub $subwallet){
		$db_transaction = Yii::$app->db->beginTransaction();

		try{
			$current_balance = WalletAPI::balance([
				'player_id'    => $this->_wallet->customer->username ?? NULL,
				'product_code' => $subwallet->product_code
			]);

			if (is_null($current_balance)){
				throw new Exception("Cannot connect to IM");
			}

			if (empty($current_balance)){
				return;
			}

			$wallet_balance = $subwallet->balance;
			if ($subwallet->balance >= $current_balance){
				$balance            = $current_balance;
				$subwallet->balance -= $balance;
			}else{
				$balance            = $subwallet->balance;
				$subwallet->balance = 0;
			}

			$balance = floor($balance);

			if ($subwallet->save()){
				$transaction = WalletSubTransaction::store([
					'wallet_sub_id' => $subwallet->id,
					'amount'        => $balance,
					'balance'       => $wallet_balance,
					'type'          => Transaction::TYPE_TRANSFER,
					'status'        => Transaction::STATUS_SUCCESS
				]);

				if (empty($transaction)){
					throw new Exception("Invalid transaction");
				}

				$withdraw = WalletAPI::transfer([
					'player_id'      => $this->_wallet->customer->username ?? NULL,
					'product_code'   => $subwallet->product_code,
					'transaction_id' => $transaction->transaction_id,
					'amount'         => $balance * - 1
				]);

				if (empty($withdraw)){
					throw new Exception("Cannot withdraw from IM");
				}

				$this->_wallet->balance += $balance;
				if ($this->_wallet->save()){
					Transaction::store([
						'wallet_id'    => $this->wallet_id,
						'amount'       => $balance,
						'balance'      => $balance,
						'type'         => Transaction::TYPE_RECEIVE,
						'reference_id' => $transaction->transaction_id,
						'status'       => Transaction::STATUS_SUCCESS
					]);

					// Store history of transfer to DB
					$history = new TransferHistory([
						'transaction_id' => $transaction->transaction_id,
						'from'           => $subwallet->id,
						'to'             => NULL,
						'amount'         => $balance,
						'customer_id'    => $subwallet->wallet->customer_id ?? NULL
					]);

					$history->save(FALSE);

					$db_transaction->commit();

					$this->_wallet->refresh();
				}
			}

		}catch (Exception|Throwable $exception){
			$db_transaction->rollBack();
		}
	}

	/**
	 * @return WalletSub[]
	 */
	private function findSubWallets()
	: array{
		if (empty($this->wallet_id)){
			return [];
		}

		if ($this->_wallet = Wallet::findOne($this->wallet_id)){
			$sub_wallets = $this->_wallet->getSubWallets()
			                             ->andWhere(['status' => WalletSub::STATUS_ACTIVE])
			                             ->andWhere(['>', 'balance', 0])
			                             ->all();
		}

		return !empty($sub_wallets) ? $sub_wallets : [];
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