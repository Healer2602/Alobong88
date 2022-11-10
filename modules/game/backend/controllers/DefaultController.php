<?php

namespace modules\game\backend\controllers;

use backend\base\Controller;
use common\base\Status;
use common\base\StatusControllerBehavior;
use modules\game\backend\models\GameForm;
use modules\game\models\Game;
use modules\game\models\GameType;
use modules\game\models\Vendor;
use modules\spider\lib\sortable\SortableControllerBehavior;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * Class DefaultController
 *
 * @package backend\controllers
 * @method sortNode()
 */
class DefaultController extends Controller{

	/**
	 * @return array
	 */
	public function behaviors(){

		$behaviors = [
			[
				'class' => StatusControllerBehavior::class,
				'model' => GameForm::class
			],
			[
				'class' => SortableControllerBehavior::class,
				'model' => GameForm::class
			],
			'access' => [
				'rules' => [
					[
						'allow'       => TRUE,
						'actions'     => ['index'],
						'permissions' => ['game'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['create', 'update', 'active', 'feature'],
						'permissions' => ['game upsert'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['delete'],
						'permissions' => ['game delete'],
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
		if ($this->request->isAjax){
			return $this->sortNode();
		}

		$filtering = $this->request->get();

		$query = GameForm::find()
		                 ->distinct()
		                 ->with(['detailZh', 'detailVi'])
		                 ->joinWith(['type type', 'vendor vendor', 'details detail'], FALSE);

		if (isset($filtering['state']) && $filtering['state'] != - 1){
			$query->andFilterWhere(['game.status' => $filtering['state'] ?? NULL]);
		}

		$query->andFilterWhere(['vendor.id' => $filtering['vendor'] ?? NULL]);
		$query->andFilterWhere(['type.id' => $filtering['type'] ?? NULL]);
		if (!empty($filtering['s'])){
			$query->andFilterWhere(['OR',
				['LIKE', 'game.name', $filtering['s']],
				['LIKE', 'game.code', $filtering['s']],
				['LIKE', 'detail.name', $filtering['s']],
			]);
		}

		$games = new ActiveDataProvider([
			'query' => $query,
			'sort'  => [
				'defaultOrder' => ['ordering' => SORT_ASC]
			]
		]);

		$filters = [
			'types'   => GameType::findList(),
			'vendors' => Vendor::findList(),
			'states'  => Status::states()
		];

		return $this->render('index', [
			'games'     => $games,
			'filters'   => $filters,
			'filtering' => $filtering
		]);
	}

	/**
	 * @return string|\yii\web\Response
	 */
	public function actionCreate(){
		$model = new GameForm();

		if ($model->load($this->request->post()) && $model->save()){
			$this->flash('success', 'Game successfully created.');

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
		$model = $this->findModel($id);

		if ($model->load($this->request->post()) && $model->save()){
			$this->flash('success', 'Game successfully updated.');

			return $this->redirect(['index']);
		}

		$model->getDataDetails();

		return $this->render('update', [
			'model' => $model,
		]);
	}

	/**
	 * @param $id
	 *
	 * @return string
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function actionActive($id){
		$model = $this->findModel($id);
		$this->changeStatus($id, $model->status);

		return '';
	}

	/**
	 * @param $id
	 *
	 * @return string
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function actionFeature($id){
		$model          = $this->findModel($id);
		$feature        = $model->feature ? NULL : Game::FEATURED;
		$model->feature = $feature;
		$model->save();

		return '';
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
		if ($model->delete()){
			$this->flash('success', 'Game has been deleted successfully');
		}

		return $this->back();
	}

	/**
	 * @param $id
	 *
	 * @return GameForm|null
	 * @throws \yii\web\NotFoundHttpException
	 */
	protected function findModel($id){
		if (($model = GameForm::findOne($id)) !== NULL){
			return $model;
		}

		throw new NotFoundHttpException('Request not found');
	}
}