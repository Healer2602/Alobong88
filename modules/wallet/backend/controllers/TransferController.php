<?php

namespace modules\wallet\backend\controllers;

use backend\base\Controller;
use common\base\AppHelper;
use modules\wallet\models\TransferHistory;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * Class TransferController
 *
 * @package modules\wallet\backend\controllers
 */
class TransferController extends Controller{

	/**
	 * {@inheritdoc}
	 */
	public function behaviors(){
		$behaviors = [
			'access' => [
				'rules' => [
					[
						'allow'       => TRUE,
						'actions'     => ['index'],
						'permissions' => ['wallet transfer'],
					],
				]
			]
		];

		return ArrayHelper::merge(parent::behaviors(), $behaviors);
	}

	/**
	 * @return string
	 */
	public function actionIndex(){
		$filtering = $this->request->get();
		$query     = TransferHistory::find()
		                            ->alias('transfer')
		                            ->joinWith('customer customer');

		if (!empty($filtering['s'])){
			$query->andFilterWhere([
				'OR',
				['LIKE', 'customer.name', $filtering['s']],
				['transaction_id' => $filtering['s']],
			]);
		}

		$query->andFilterWhere(['customer_id' => $filtering['player'] ?? NULL]);

		if (!empty($filtering['date'])){
			$range = AppHelper::parseDateRange($filtering['date']);

			if (!empty($range[0])){
				$query->andFilterWhere(['>=', 'transfer.created_at', $range[0]]);
			}
			if (!empty($range[1])){
				$query->andFilterWhere(['<=', 'transfer.created_at', $range[1]]);
			}
		}

		$data = new ActiveDataProvider([
			'query' => $query,
			'sort'  => [
				'defaultOrder' => [
					'created_at' => SORT_DESC
				]
			]
		]);

		return $this->render('index', [
			'data'      => $data,
			'filtering' => $filtering,
			'filters'   => [
				'players' => TransferHistory::players()
			]
		]);
	}
}