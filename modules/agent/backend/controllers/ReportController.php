<?php

namespace modules\agent\backend\controllers;

use backend\base\Controller;
use modules\agent\models\Agent;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * ReportController implements the CRUD actions for agent model.
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
						'actions'     => ['index'],
						'permissions' => ['report agent'],
					],
				]
			]
		];

		return ArrayHelper::merge(parent::behaviors(), $behaviors);
	}

	/**
	 * Lists all Agent models.
	 *
	 * @return mixed
	 */
	public function actionIndex(){
		$from = $this->request->get('from', date('Y-m', strtotime('first day of last month')));
		$to   = $this->request->get('to', $from);

		$query = Agent::find()
		              ->joinWith(['reports' => function (ActiveQuery $query) use ($from, $to){
			              $query->andOnCondition(['>=', 'date', $from . '-01']);
			              $query->andOnCondition(['<=', 'date', $to . '-01']);
		              }]);

		$agents = new ActiveDataProvider([
			'query'      => $query,
			'pagination' => [
				'pageSizeLimit' => [1, 100]
			]
		]);

		return $this->render('index', [
			'agents'    => $agents,
			'filtering' => $this->request->get()
		]);
	}
}
