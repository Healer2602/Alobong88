<?php

namespace modules\agent\console;

use common\base\Status;
use modules\agent\models\Agent;
use modules\agent\models\AgentReport;
use modules\investment\models\Invest;
use modules\wallet\models\Transaction;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\ArrayHelper;

/**
 * Class CommissionController
 */
class CommissionController extends Controller{

	public $date = NULL;

	/**
	 * @param string $actionID
	 *
	 * @return string[]
	 */
	public function options($actionID){
		return ['date'];
	}


	/**
	 * @return int
	 */
	public function actionIndex(){
		$date = $this->date;
		if (empty($date)){
			$date = date('Y-m-d');
		}

		$first_date = date('Y-m-d', strtotime($date . ' first day of last month'));
		$end_date   = date('Y-m-t', strtotime($first_date));

		$agents = Agent::find()
		               ->with(['assignedCustomers' => function ($query){
			               $query->andWhere(['status' => Status::STATUS_ACTIVE]);
		               }])
		               ->with('customer.wallet')
		               ->default()->all();

		$start_time = strtotime($first_date);
		$end_time   = strtotime($end_date . ' 23:59:59');
		$total      = 0;

		foreach ($agents as $agent){
			if (empty($agent->commission)){
				continue;
			}

			$wallet = $agent->customer->wallet ?? NULL;
			if (empty($wallet)){
				continue;
			}

			$customers = ArrayHelper::getColumn($agent->assignedCustomers, 'id');
			if (empty($customers)){
				continue;
			}

			$query = Invest::find()
			               ->andWhere(['customer_id' => $customers])
			               ->andWhere(['>=', 'updated_at', $start_time])
			               ->andWhere(['<=', 'updated_at', $end_time]);

			$active_query = clone $query;
			$active_users = $active_query
				->select(['customer_id'])
				->distinct()
				->count();

			if ($active_users < $agent->active_users){
				continue;
			}

			$invests = $query->with('customer')
			                 ->andWhere(['status' => Invest::STATUS_CONFIRMED])
			                 ->andWhere(['>', 'profit', 0])
			                 ->asArray()
			                 ->all();

			if (!empty($invests)){
				foreach ($invests as $invest){
					$reference_id = Invest::generateID($invest['id']);
					$has_shared   = Transaction::find()
					                           ->andWhere(['type' => Transaction::TYPE_REWARD])
					                           ->andWhere(['reference_id' => $reference_id])
					                           ->exists();

					if (empty($has_shared)){
						$company_profit  = $invest['profit'] * $invest['final_successful_rate'] / (100 - $invest['final_successful_rate']);
						$amount          = $agent->commission / 100 * $company_profit;
						$wallet->balance += $amount;
						if ($wallet->save(FALSE)){
							$transaction = new Transaction([
								'amount'       => $amount,
								'status'       => Transaction::STATUS_SUCCESS,
								'wallet_id'    => $agent->customer->wallet->id ?? NULL,
								'type'         => Transaction::TYPE_REWARD,
								'reference_id' => $reference_id,
								'description'  => Yii::t('agent', "Commission from {0}",
									[$invest['customer']['email']])
							]);

							if ($transaction->save()){
								$total ++;

								AgentReport::upsert($agent->id, $first_date, $active_users,
									$company_profit, $amount);
							}
						}
					}
				}
			}
		}

		echo "Total {$total} commissions have been shared with agents.\n";

		return ExitCode::OK;
	}
}
