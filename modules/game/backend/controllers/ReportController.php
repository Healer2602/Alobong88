<?php

namespace modules\game\backend\controllers;

use backend\base\Controller;
use common\base\AppHelper;
use modules\game\models\Betlog;
use modules\game\models\GamePlay;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * Class BetlogController
 *
 * @package modules\wallet\backend\controllers
 */
class ReportController extends Controller{

	/**
	 * {@inheritdoc}
	 */
	public function behaviors(){
		$behaviors = [
			'access' => [
				'rules' => [
					[
						'allow'       => TRUE,
						'actions'     => ['betlog'],
						'permissions' => ['game betlog'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['play'],
						'permissions' => ['game play'],
					],
				]
			]
		];

		return ArrayHelper::merge(parent::behaviors(), $behaviors);
	}

	/**
	 * @return string
	 */
	public function actionBetlog(){
		$filtering = $this->request->get();
		$query     = Betlog::find()
		                   ->alias('betlog')
		                   ->select([
			                   'betlog.id',
			                   'bet_count'         => 'COUNT(*)',
			                   'amount'            => 'SUM(amount)',
			                   'winloss'           => 'SUM(winloss)',
			                   'bonus'             => new Expression('SUM(bonus) * COUNT(DISTINCT promotion_id) / COUNT(*)'),
			                   'total_rebate'      => 'SUM(total_rebate)',
			                   'turnover_bonus'    => 'SUM(turnover_bonus)',
			                   'turnover_wo_bonus' => 'SUM(turnover_wo_bonus)',
			                   'betlog.updated_at',
			                   'betlog.created_at',
			                   'provider',
			                   'player_id'
		                   ])
		                   ->joinWith(['customer customer', 'vendor vendor'])
		                   ->groupBy(['player_id', 'provider']);

		if (!empty($filtering['s'])){
			$query->andFilterWhere([
				'OR',
				['LIKE', 'customer.name', $filtering['s']],
				['vendor.name' => $filtering['s']]
			]);
		}

		if (!empty($filtering['date'])){
			$range = AppHelper::parseDateRange($filtering['date']);

			if (!empty($range[0])){
				$query->andFilterWhere(['>=', 'betlog.created_at', $range[0]]);
			}
			if (!empty($range[1])){
				$query->andFilterWhere(['<=', 'betlog.created_at', $range[1]]);
			}
		}

		$data = new ActiveDataProvider([
			'query' => $query
		]);

		$sort = $data->getSort();

		$sort->attributes['bet_count'] = [
			SORT_ASC  => "COUNT(*) ASC",
			SORT_DESC => "COUNT(*) DESC",
		];

		$sort->defaultOrder = ['updated_at' => SORT_DESC];
		$data->setSort($sort);

		return $this->render('betlog', [
			'data'      => $data,
			'filtering' => $filtering
		]);
	}

	/**
	 * @return string
	 */
	public function actionPlay(){
		$filtering = $this->filtering();
		$query     = GamePlay::find()
		                     ->alias('play')
		                     ->joinWith(['player player', 'game game']);

		if (!empty($filtering['s'])){
			$query->andFilterWhere([
				'OR',
				['LIKE', 'player.name', $filtering['s']],
				['game.name' => $filtering['s']],
				['game.code' => $filtering['s']],
			]);
		}

		if (!empty($filtering['date'])){
			$range = AppHelper::parseDateRange($filtering['date']);

			if (!empty($range[0])){
				$query->andFilterWhere(['>=', 'play.last_play', $range[0]]);
			}
			if (!empty($range[1])){
				$query->andFilterWhere(['<=', 'play.last_play', $range[1]]);
			}
		}

		$data = new ActiveDataProvider([
			'query' => $query,
			'sort'  => [
				'defaultOrder' => ['last_play' => SORT_DESC]
			]
		]);

		return $this->render('play', [
			'data'      => $data,
			'filtering' => $filtering
		]);
	}
}