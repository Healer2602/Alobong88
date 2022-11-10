<?php

namespace modules\wallet\frontend\controllers;

use common\base\AppHelper;
use frontend\base\Controller;
use modules\customer\frontend\models\CustomerIdentity;
use modules\wallet\frontend\models\Betlog;
use modules\wallet\models\Transaction;
use modules\wallet\models\TransferHistory;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * HistoryController wallet
 */
class HistoryController extends Controller{

	/**
	 * {@inheritdoc}
	 */
	public function behaviors(){
		$behaviors = [
			'access' => [
				'rules' => [
					[
						'roles' => ['@'],
						'allow' => TRUE,
					],
				],
			]
		];

		return ArrayHelper::merge(parent::behaviors(), $behaviors);
	}

	/**
	 * @return string
	 */
	public function actionIndex(){
		$filtering = $this->request->get();

		$query = Betlog::find()
		               ->select([
			               'id',
			               'bet_count'         => 'COUNT(*)',
			               'amount'            => 'SUM(amount)',
			               'winloss'           => 'SUM(winloss)',
			               'bonus'             => 'SUM(bonus)',
			               'total_rebate'      => 'SUM(total_rebate)',
			               'turnover_bonus'    => 'SUM(turnover_bonus)',
			               'turnover_wo_bonus' => 'SUM(turnover_wo_bonus)',
			               'provider'
		               ])
		               ->andWhere(['player_id' => Yii::$app->user->identity->username])
		               ->with(['customer', 'vendor'])
		               ->groupBy(['provider', 'player_id']);

		if (!empty($filtering['date'])){
			$range = AppHelper::parseDateRange($filtering['date']);
			if (!empty($range[0])){
				$query->andFilterWhere(['>=', 'created_at', $range[0]]);
			}
			if (!empty($range[1])){
				$query->andFilterWhere(['<=', 'created_at', $range[1]]);
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

		return $this->render('index', [
			'data'      => $data,
			'filtering' => $filtering
		]);
	}

	/**
	 * @return string
	 */
	public function actionTransfer(){
		$filtering = $this->request->get();
		$query     = TransferHistory::find()
		                            ->andWhere(['customer_id' => Yii::$app->user->id])
		                            ->with(['fromWallet', 'toWallet', 'customer']);

		if (!empty($filtering['date'])){
			$range = AppHelper::parseDateRange($filtering['date']);
			if (!empty($range[0])){
				$query->andFilterWhere(['>=', 'created_at', $range[0]]);
			}
			if (!empty($range[1])){
				$query->andFilterWhere(['<=', 'created_at', $range[1]]);
			}
		}

		$data = new ActiveDataProvider([
			'query' => $query,
			'sort'  => [
				'defaultOrder' => ['id' => SORT_DESC]
			]
		]);

		return $this->render('transfer', [
			'data'      => $data,
			'filtering' => $filtering
		]);
	}

	/**
	 * @return string
	 */
	public function actionLog(){
		$filtering = $this->request->get();
		$query     = Transaction::find()
		                        ->andWhere(['wallet_id' => CustomerIdentity::profile()->wallet->id ?? NULL])
		                        ->andWhere(['type' => [Transaction::TYPE_WITHDRAW, Transaction::TYPE_TOPUP]])
		                        ->with('gateway');

		if (!empty($filtering['date'])){
			$range = AppHelper::parseDateRange($filtering['date']);
			if (!empty($range[0])){
				$query->andFilterWhere(['>=', 'created_at', $range[0]]);
			}
			if (!empty($range[1])){
				$query->andFilterWhere(['<=', 'created_at', $range[1]]);
			}
		}

		$data = new ActiveDataProvider([
			'query' => $query,
			'sort'  => [
				'defaultOrder' => ['id' => SORT_DESC]
			]
		]);

		return $this->render('log', [
			'data'      => $data,
			'filtering' => $filtering
		]);
	}
}