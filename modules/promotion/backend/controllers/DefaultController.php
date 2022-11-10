<?php

namespace modules\promotion\backend\controllers;

use backend\base\Controller;
use common\base\AppHelper;
use common\base\Status;
use common\base\StatusControllerBehavior;
use modules\promotion\console\CancelPromoJob;
use modules\promotion\console\DeletePromoJob;
use modules\promotion\models\Promotion;
use modules\promotion\models\PromotionJoining;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * NewsController implements the CRUD actions for News model.
 */
class DefaultController extends Controller{

	/**
	 * {@inheritdoc}
	 */
	public function behaviors(){
		$behaviors = [
			[
				'class' => StatusControllerBehavior::class,
				'model' => Promotion::class
			],
			'verbs'  => [
				'actions' => [
					'cancel' => ['post'],
				],
			],
			'access' => [
				'rules' => [
					[
						'allow'       => TRUE,
						'actions'     => ['index'],
						'permissions' => ['promotion'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['active', 'create', 'update'],
						'permissions' => ['promotion upsert'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['delete'],
						'permissions' => ['promotion delete'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['players'],
						'permissions' => ['promotion players'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['cancel'],
						'permissions' => ['promotion cancel'],
					],
				]
			]
		];

		return ArrayHelper::merge(parent::behaviors(), $behaviors);
	}

	/**
	 * @return string
	 * @throws \Throwable
	 */
	public function actionIndex(){
		$filtering = $this->request->get();
		$query     = Promotion::find()->notDeleted();

		if (isset($filtering['state']) && $filtering['state'] != - 1){
			$query->andFilterWhere(['status' => $filtering['state'] ?? NULL]);
		}

		if (!empty($filtering['s'])){
			$query->andFilterWhere(['LIKE', 'name', $filtering['s']]);
		}

		if (isset($filtering['type'])){
			$query->andFilterWhere(['type' => $filtering['type'] ?? NULL]);
		}

		if (!empty($filtering['date'])){
			$range = AppHelper::parseDateRange($filtering['date']);

			if (!empty($range[0])){
				$query->andFilterWhere(['<=', 'start_date', $range[0]]);
			}
			if (!empty($range[1])){
				$query->andFilterWhere(['>=', 'end_date', $range[1]]);
			}
		}

		$data = new ActiveDataProvider([
			'query' => $query,
			'sort'  => [
				'defaultOrder' => ['created_at' => SORT_ASC]
			]
		]);

		$filters = [
			'types'  => Promotion::types(),
			'states' => Promotion::statuses()
		];

		return $this->render('index', [
			'data'      => $data,
			'filtering' => $filtering,
			'filters'   => $filters,
		]);
	}

	/**
	 * @param $type
	 *
	 * @return string|\yii\web\Response
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function actionCreate($type){
		if (!ArrayHelper::keyExists($type, Promotion::types())){
			throw new NotFoundHttpException('Request not found');
		}

		$model = new Promotion([
			'type' => $type
		]);

		if ($model->load($this->request->post()) && $model->save()){
			$this->flash('success', 'Promotion successfully created.');

			return $this->redirect(['update', 'id' => $model->id]);
		}

		return $this->render('create', [
			'model' => $model,
		]);
	}

	/**
	 * @param $id
	 *
	 * @return string|\yii\web\Response
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function actionUpdate($id){
		$model                 = $this->findModel($id);
		$model->product_wallet = ArrayHelper::getColumn($model->productMap,
			'product_code');

		if ($model->load($this->request->post()) && $model->save()){
			$this->flash('success', 'Promotion successfully updated.');

			return $this->redirect(['index']);
		}

		return $this->render('update', [
			'model' => $model,
		]);
	}

	/**
	 * @param $id
	 *
	 * @return \yii\web\Response
	 * @throws \Throwable
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function actionDelete($id){
		$model = $this->findModel($id);
		if ($model->status != Status::STATUS_DELETED){
			$delete = $model->updateAttributes(['status' => Status::STATUS_DELETED]);
			if ($delete){
				/**@var \common\base\Queue $queue */
				$queue = Yii::$app->queue;

				$queue->push(new DeletePromoJob([
					'promotion_id' => $id
				]));

				$this->flash('success', 'Promotion has been deleted successfully');
			}
		}

		return $this->back();
	}

	/**
	 * @param $id
	 *
	 * @return Promotion|null
	 * @throws \yii\web\NotFoundHttpException
	 */
	protected function findModel($id){
		if (($model = Promotion::findOne($id)) !== NULL){
			return $model;
		}

		throw new NotFoundHttpException('Request not found');
	}

	/**
	 * @return string
	 * @throws \Throwable
	 */
	public function actionPlayers(){
		$filtering = $this->request->get();

		$query = PromotionJoining::find()
		                         ->alias('joining')
		                         ->select([
			                         'joining.*',
			                         'totalTurnover' => 'SUM(turnover.turnover)',
			                         'round'         => 'SUM(turnover.round)',
		                         ])
		                         ->joinWith('promotion promotion')
		                         ->joinWith('turnover turnover', FALSE)
		                         ->with(['player'])
		                         ->groupBy(['promotion_id', 'player_id']);

		if (isset($filtering['state']) && $filtering['state'] != - 1){
			$query->andFilterWhere(['promotion.status' => $filtering['state'] ?? NULL]);
		}

		if (!empty($filtering['s'])){
			$query->andFilterWhere(['promotion.name' => $filtering['s'] ?? NULL]);
		}

		if (!empty($filtering['date'])){
			$range = AppHelper::parseDateRange($filtering['date']);

			if (!empty($range[0])){
				$query->andFilterWhere(['<=', 'joined_at', $range[0]]);
			}
			if (!empty($range[1])){
				$query->andFilterWhere(['>=', 'joined_at', $range[1]]);
			}
		}

		$data = new ActiveDataProvider([
			'query' => $query,
			'sort'  => [
				'defaultOrder' => ['id' => SORT_DESC]
			]
		]);

		$filters = [
			'states' => Promotion::statuses()
		];

		return $this->render('players', [
			'data'      => $data,
			'filtering' => $filtering,
			'filters'   => $filters,
		]);
	}

	/**
	 * @param $id
	 *
	 * @return \yii\web\Response
	 */
	public function actionCancel($id){
		$join = PromotionJoining::find()
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
		                        ->andWhere(['joining.id' => $id])
		                        ->groupBy(['promotion_id', 'player_id'])
		                        ->one();

		if (!empty($join) && $join->canCancel()){
			/**@var \common\base\Queue $queue */
			$queue = Yii::$app->queue;

			$added = $queue->push(new CancelPromoJob([
				'join'   => $join,
				'status' => PromotionJoining::STATUS_CANCELED
			]));

			if ($added){
				$this->flash('success',
					'Canceling promotion has been processed. Please be patient');
			}
		}

		return $this->redirect(['players']);
	}
}