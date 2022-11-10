<?php

namespace modules\wallet\console;

use common\models\AuditTrail;
use Exception;
use modules\game\models\GamePlay;
use modules\matrix\base\Wallet;
use modules\wallet\console\jobs\RestoreJob;
use modules\wallet\models\Setting;
use modules\wallet\models\Transaction;
use modules\wallet\models\Wallet as WalletModel;
use modules\wallet\models\WalletSub;
use modules\wallet\models\WalletSubTransaction;
use Throwable;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * Class CronController
 */
class CronController extends Controller{

	/**
	 * @return int
	 */
	public function actionIndex(){
		$setting = new Setting();
		$setting->getValues();

		if (!empty($setting->topup_auto_reject)){
			$total = Transaction::updateAll(['status' => Transaction::STATUS_FAILED], [
				'AND', ['status' => Transaction::STATUS_PENDING, 'type' => Transaction::TYPE_TOPUP], ['<', 'updated_at', time() - $setting->topup_auto_reject * 3600]
			]);

			if (!empty($total)){
				$message = Yii::t('wallet',
					"Total {0} deposit transactions have been cancelled automatically.", [$total]);
				AuditTrail::log('Auto Cancel', $message, 'eWallet');

				echo $message . "\n";
			}else{
				echo "No transactions need to be cancelled.";
			}
		}

		return ExitCode::OK;
	}

	/**
	 * Sync wallet balance from IM to sub-wallet
	 *
	 * @return int
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\httpclient\Exception
	 */
	public function actionTransfer(){
		$setting = new \modules\matrix\models\Setting();
		$setting->getValues();
		$last_active = $setting->interval_wallet;
		if (empty($last_active)){
			$last_active = 2880;
		}

		$plays = GamePlay::find()
		                 ->andWhere(['>=', 'last_play', time() - $last_active * 60])
		                 ->with(['wallet.customer'])
		                 ->orderBy(['last_play' => SORT_DESC])
		                 ->all();

		$wallet_ids = [];
		$total      = 0;

		if (!empty($plays)){
			foreach ($plays as $play){
				$wallet_ids[] = $play->wallet->id ?? NULL;

				if ($this->withdraw($play->wallet)){
					$total ++;
				}
			}
		}

		$wallet_query = WalletSub::find()
		                         ->andWhere(['>=', 'last_update', time() - $last_active * 60 - 86400])
		                         ->with(['customer'])
		                         ->orderBy(['last_update' => SORT_DESC]);

		if (!empty($wallet_ids)){
			$wallet_query->andWhere(['NOT', ['id' => $wallet_ids]]);
		}

		$wallets = $wallet_query->all();
		foreach ($wallets as $wallet){
			if ($this->withdraw($wallet)){
				$total ++;
			}
		}

		$message = Yii::t('wallet', 'Update balance {0} sub-wallets from IM', $total);

		if (!empty($total)){
			AuditTrail::log('Update', $message, 'Wallet');
		}

		echo $message . "\n";

		return ExitCode::OK;
	}

	/**
	 * @param \modules\wallet\models\WalletSub $wallet
	 *
	 * @return boolean
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\httpclient\Exception
	 */
	private function withdraw($wallet){
		if (empty($wallet)){
			return FALSE;
		}

		if ($player = $wallet->customer){
			$balance = Wallet::balance([
				'player_id'    => $player->username ?? NULL,
				'product_code' => $wallet->product_code
			]);

			if (!is_null($balance) && $wallet->balance != $balance){
				$db_transaction = Yii::$app->db->beginTransaction();
				try{
					$old_balance     = $wallet->balance;
					$wallet->balance = $balance;

					if ($wallet->save()){
						$tnx = WalletSubTransaction::store([
							'wallet_sub_id' => $wallet->id,
							'amount'        => $balance - $old_balance,
							'balance'       => $wallet->balance,
							'type'          => Transaction::TYPE_PLAY,
							'status'        => Transaction::STATUS_SUCCESS
						]);

						if (!empty($tnx)){
							$db_transaction->commit();

							return TRUE;
						}
					}

				}catch (Exception|Throwable $exception){
					$db_transaction->rollBack();
				}
			}
		}

		return FALSE;
	}

	/**
	 * @return int
	 */
	public function actionAutoTransfer(){
		$wallets = WalletModel::find()
		                      ->andWhere(['auto_transfer' => TRUE])
		                      ->andWhere(['status' => WalletModel::STATUS_ACTIVE])
		                      ->select('id')
		                      ->column();

		foreach ($wallets as $wallet){
			/**@var \common\base\Queue $queue */
			$queue = Yii::$app->queue;

			$queue->push(new RestoreJob([
				'wallet_id' => $wallet
			]));
		}

		return ExitCode::OK;
	}
}
