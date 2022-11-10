<?php

namespace modules\promotion\console;

use modules\promotion\models\Promotion;
use modules\promotion\models\PromotionJoining;
use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;

/**
 * Restore wallet job
 */
class DeletePromoJob extends BaseObject implements JobInterface{

	public $promotion_id;

	/**
	 * @inheritDoc
	 */
	public function execute($queue){
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
		                         ->andWhere(['joining.promotion_id' => $this->promotion_id])
		                         ->groupBy(['promotion_id', 'player_id'])
		                         ->all();

		foreach ($joins as $join){
			/**@var \common\base\Queue $queue */
			$queue = Yii::$app->queue;

			$queue->push(new CancelPromoJob([
				'join'   => $join,
				'status' => PromotionJoining::STATUS_CANCELED
			]));
		}
	}
}