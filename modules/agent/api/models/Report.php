<?php

namespace modules\agent\api\models;

use common\base\Status;
use modules\customer\models\Customer;
use modules\game\models\Turnover;
use modules\wallet\models\Wallet;
use yii\base\Model;

/**
 * Agent Report
 *
 * @property-read \modules\agent\models\Agent $agent
 */
class Report extends Model{

	public $code;
	public $start_date;
	public $to_date;

	/**
	 * @return array
	 */
	public function rules()
	: array{
		return [
			['code', 'string'],
			['code', 'required'],
			[['start_date', 'to_date'], 'safe']
		];
	}

	private $_agent = NULL;

	/**
	 * @return \modules\agent\models\Agent|null
	 */
	public function getAgent(){
		if ($this->_agent === NULL){
			$this->_agent = \modules\agent\models\Agent::findByCode($this->code);
		}

		return $this->_agent;
	}

	/**
	 * @return array
	 */
	public function getSummary(){
		if ($agent = $this->agent){
			if (empty($this->start_date)){
				$this->start_date = strtotime(date("Y-m-01"));
				$this->to_date    = strtotime(date("Y-m-t"));
			}else{
				$this->start_date = strtotime($this->start_date);
				if (!empty($this->to_date)){
					$this->to_date = strtotime($this->to_date) + 86400;
				}else{
					$this->to_date = time();
				}
			}

			$user_query = Customer::find()
			                      ->andWhere(['agent_id' => $agent->id, 'status' => Status::STATUS_ACTIVE]);

			$total_users         = $user_query->count();
			$current_month_users = $user_query->andWhere(['>=', 'created_at', $this->start_date])
			                                  ->andWhere(['<', 'created_at', $this->to_date])
			                                  ->count();

			$active_query = Turnover::find()
			                        ->alias('turnover')
			                        ->joinWith('player player', FALSE, 'RIGHT JOIN')
			                        ->andWhere(['player.agent_id' => $agent->id, 'player.status' => Status::STATUS_ACTIVE])
			                        ->groupBy('player.id')
			                        ->having(['>', 'SUM(turnover.winloss)', 0]);

			$total_active_users         = $active_query->count("DISTINCT player_id");
			$current_month_active_users = $active_query->andWhere(['>=', 'player.created_at', $this->start_date])
			                                           ->andWhere(['<', 'player.created_at', $this->to_date])
			                                           ->count("DISTINCT player_id");

			$turnover_query = Turnover::find()
			                          ->select(['winloss' => 'SUM(turnover.winloss)', 'turnover' => 'SUM(turnover.turnover)'])
			                          ->alias('turnover')
			                          ->joinWith('player player', FALSE, 'RIGHT JOIN')
			                          ->andWhere(['player.agent_id' => $agent->id, 'player.status' => Status::STATUS_ACTIVE]);

			$turnover       = $turnover_query->asArray()->one();
			$turnover_month = $turnover_query->andWhere(['>=', 'UNIX_TIMESTAMP(turnover.date)', $this->start_date])
			                                 ->andWhere(['<', 'UNIX_TIMESTAMP(turnover.date)', $this->to_date])
			                                 ->asArray()
			                                 ->one();

			$deposit = Customer::find()
			                   ->joinWith('wallet wallet', FALSE)
			                   ->andWhere(['agent_id' => $agent->id, 'customer.status' => Status::STATUS_ACTIVE])
			                   ->andWhere(['>=', 'customer.created_at', $this->start_date])
			                   ->andWhere(['<', 'customer.created_at', $this->to_date])
			                   ->groupBy('customer.id')
			                   ->having(['>', 'SUM(wallet.total_deposit)', 0])
			                   ->count();

			$total_deposit = Wallet::find()
			                       ->alias('wallet')
			                       ->joinWith('customer customer', FALSE)
			                       ->andWhere(['customer.agent_id' => $agent->id, 'customer.status' => Status::STATUS_ACTIVE])
			                       ->andWhere(['>=', 'customer.created_at', $this->start_date])
			                       ->andWhere(['<', 'customer.created_at', $this->to_date])
			                       ->groupBy('customer.id')
			                       ->sum("total_deposit") ?: 0;

			return [
				'total'  => [
					'users'          => $total_users,
					'active_users'   => $total_active_users,
					'inactive_users' => $total_users - $total_active_users,
					'turnovers'      => $turnover['turnover'] ?? 0
				],
				'month'  => [
					'users'          => $current_month_users,
					'active_users'   => $current_month_active_users,
					'inactive_users' => $current_month_users - $current_month_active_users,
					'turnovers'      => $turnover_month['turnover'] ?? 0,
					'winloss'        => $turnover_month['winloss'] ?? 0,
					'deposits'       => $deposit,
					'total_deposit'  => $total_deposit
				],
				'status' => [
					'id'   => $agent->status,
					'name' => $agent->getStatusHtml()
				]
			];
		}

		return [];
	}
}