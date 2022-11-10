<?php

namespace modules\promotion\console;

use Exception;
use modules\promotion\models\Promotion;
use modules\promotion\models\PromotionJoining;
use modules\wallet\models\Wallet;
use modules\wallet\models\WalletSub;
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
		$joins = PromotionJoining::find()
		                         ->alias('joining')
		                         ->select([
			                         'joining.*',
			                         'totalTurnover' => 'SUM(turnover.turnover)',
			                         'round'         => 'SUM(turnover.round)',
			                         'totalWin'      => 'SUM(turnover.win)',
			                         'total'         => '(1 + [[rate]]) * [[bonus]]',
		                         ])
		                         ->joinWith('promotion promotion')
		                         ->joinWith('turnover turnover', FALSE)
		                         ->andWhere(['joining.status' => PromotionJoining::STATUS_RUNNING])
		                         ->andWhere(['joining.promotion_type' => Promotion::TYPE_FIRST_DEPOSIT])
		                         ->groupBy(['promotion_id', 'player_id'])
		                         ->all();

		$total_win  = 0;
		$total_lost = 0;

		foreach ($joins as $join){
			$min_turnover = $join->total * $join->promotion->min_round;

			if ($min_turnover <= floatval($join->totalTurnover)){
				$join->status = PromotionJoining::STATUS_DONE;
				$transaction  = Yii::$app->db->beginTransaction();

				try{
					if ($join->save()){
						$join->wallet->status = WalletSub::STATUS_ACTIVE;
						if ($join->wallet->save()){
							Wallet::storeTurnover($join->player_id, floatval($join->totalTurnover));

							$transaction->commit();
							$total_win ++;
						}
					}
				}catch (Exception|Throwable $exception){
					$transaction->rollBack();
				}
			}elseif ($join->joined_at <= strtotime('-30days')){
				/**@var \common\base\Queue $queue */
				$queue = Yii::$app->queue;

				$queue->push(new CancelPromoJob([
					'join' => $join
				]));

				$total_lost ++;
			}
		}

		if (!empty($total_lost) || !empty($total_win)){
			$message = Yii::t('promotion',
				"Total {0} promotion joining updated ({1} completed, {2} failed).",
				[$total_win + $total_lost, $total_win, $total_lost]);

			echo $message . "\n";
		}

		return ExitCode::OK;
	}
}
