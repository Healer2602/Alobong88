<?php

namespace modules\customer\backend\controllers;

use backend\base\Controller;
use modules\customer\models\Referral;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * ReportController implements the CRUD actions for referral model.
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
						'permissions' => ['customer report referral'],
					],
				]
			]
		];

		return ArrayHelper::merge(parent::behaviors(), $behaviors);
	}

	/**
	 * Lists all Referral models.
	 *
	 * @return mixed
	 */
	public function actionIndex(){
		$from = $this->request->get('from', date('Y-m', strtotime('first day of last month')));
		$to   = $this->request->get('to', $from);

		$query = Referral::find()
		                 ->joinWith(['reports' => function (ActiveQuery $query) use ($from, $to){
			                 $query->andOnCondition(['>=', 'date', $from . '-01']);
			                 $query->andOnCondition(['<=', 'date', $to . '-01']);
		                 }]);

		$referrals = new ActiveDataProvider([
			'query'      => $query,
		]);

		return $this->render('index', [
			'referrals' => $referrals,
			'filtering' => $this->request->get()
		]);
	}
}
