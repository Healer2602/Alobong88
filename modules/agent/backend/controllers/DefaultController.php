<?php

namespace modules\agent\backend\controllers;

use backend\base\Controller;
use common\base\AppHelper;
use common\base\StatusControllerBehavior;
use modules\agent\models\Agent;
use modules\game\models\Turnover;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * DefaultController implements the CRUD actions for agent model.
 */
class DefaultController extends Controller{

	/**
	 * {@inheritdoc}
	 */
	public function behaviors(){
		$behaviors = [
			[
				'class' => StatusControllerBehavior::class,
				'model' => Agent::class
			],
			'access' => [
				'rules' => [
					[
						'allow'       => TRUE,
						'actions'     => ['index'],
						'permissions' => ['agent'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['details'],
						'permissions' => ['agent details'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['delete'],
						'permissions' => ['agent delete'],
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
		$filtering = $this->request->get();
		$query     = Agent::find()
		                  ->distinct()
		                  ->joinWith('customers customer', FALSE)
		                  ->select(['agent.*', 'total' => 'COUNT(customer.id)'])
		                  ->groupBy(['agent.id']);

		if (isset($filtering['state']) && $filtering['state'] != - 1){
			$query->andFilterWhere(['status' => $filtering['state'] ?? NULL]);
		}

		if (!empty($filtering['s'])){
			$query->andFilterWhere(['OR', ['LIKE', 'name', $filtering['s']], ['LIKE', 'email', $filtering['s']]]);
		}

		$agents = new ActiveDataProvider([
			'query' => $query
		]);

		$sort = $agents->getSort();

		$sort->attributes['total'] = [
			SORT_ASC  => "COUNT(customer.id) ASC",
			SORT_DESC => "COUNT(customer.id) DESC",
		];

		$sort->defaultOrder = ['id' => SORT_DESC];
		$agents->setSort($sort);

		$filters['states'] = Agent::statuses();

		return $this->render('index', [
			'agents'    => $agents,
			'filters'   => $filters,
			'filtering' => $filtering
		]);
	}

	/**
	 * Updates an existing Agent model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 *
	 * @param integer $id
	 *
	 * @return mixed
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	public function actionDetails($id){
		$model     = $this->findModel($id);
		$filtering = $this->filtering();

		if ($model->load(Yii::$app->request->post()) && $model->save()){
			$this->flash('success', 'Agent has been updated successfully');

			return $this->refresh();
		}

		$query = Turnover::find()
		                 ->alias('turnover')
		                 ->select([
			                 'turnover.id',
			                 'turnover.player_id',
			                 'turnover'   => 'SUM(turnover.turnover)',
			                 'winloss'    => 'SUM(turnover.winloss)',
			                 'updated_at' => 'MAX(turnover.updated_at)',
			                 'player.name',
			                 'player.email'
		                 ])
		                 ->joinWith('player player', FALSE, 'RIGHT JOIN')
		                 ->andWhere(['player.agent_id' => $id])
		                 ->groupBy(['player.id']);

		if (!empty($filtering['s'])){
			$query->andWhere(['OR', ['LIKE', 'player.name', $filtering['s']], ['LIKE', 'player.email', $filtering['s']]]);
		}

		if ($date = $filtering['date'] ?? NULL){
			$dates = AppHelper::parseDateRange($date);
			if (!empty($dates[0])){
				$query->andWhere(['>=', 'turnover.date', Yii::$app->formatter->asDate($dates[0],
					'php:Y-m-d')]);
			}

			if (!empty($dates[1])){
				$query->andWhere(['<=', 'turnover.date', Yii::$app->formatter->asDate($dates[1],
					'php:Y-m-d')]);
			}elseif (!empty($dates[0])){
				$query->andWhere(['<=', 'turnover.date', Yii::$app->formatter->asDate($dates[0],
					'php:Y-m-d')]);
			}
		}

		$data = new ActiveDataProvider([
			'query'      => $query,
			'pagination' => [
				'pageSizeLimit' => [1, 100]
			],
			'sort'       => [
				'attributes' => [
					'player.name',
					'player.email',
					'balance',
					'winloss',
					'turnover.updated_at'
				]
			]
		]);

		return $this->render('details', [
			'model'     => $model,
			'customers' => $data,
			'filtering' => $filtering
		]);
	}

	/**
	 * Deletes an existing Agent model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 *
	 * @param integer $id
	 *
	 * @return mixed
	 * @throws \Throwable
	 * @throws \yii\db\StaleObjectException
	 * @throws \yii\web\NotFoundHttpException if the model cannot be found
	 */
	public function actionDelete($id){
		$this->findModel($id)->delete();
		$this->flash('success', 'Agent has been deleted successfully');

		return $this->back();
	}

	/**
	 * Finds the Agent model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 *
	 * @param integer $id
	 *
	 * @return Agent the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($id){
		if (($model = Agent::findOne($id)) !== NULL){
			return $model;
		}

		throw new NotFoundHttpException(Yii::t('common', 'The requested page does not exist.'));
	}

	/**
	 * @param $id
	 *
	 * @return \yii\web\Response
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function actionActive($id){
		$model = $this->findModel($id);
		$this->changeStatus($id, $model->status);

		return $this->back();
	}
}
