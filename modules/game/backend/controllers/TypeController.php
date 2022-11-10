<?php

namespace modules\game\backend\controllers;

use backend\base\Controller;
use common\base\Status;
use common\base\StatusControllerBehavior;
use modules\game\models\GameType;
use modules\spider\lib\sortable\SortableControllerBehavior;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * Class TypeController
 *
 * @package backend\controllers
 * @method sortNode()
 */
class TypeController extends Controller{

	/**
	 * @return array
	 */
	public function behaviors(){

		$behaviors = [
			[
				'class' => StatusControllerBehavior::class,
				'model' => GameType::class
			],
			[
				'class' => SortableControllerBehavior::class,
				'model' => GameType::class
			],
			'access' => [
				'rules' => [
					[
						'allow'       => TRUE,
						'actions'     => ['index'],
						'permissions' => ['game type'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['create', 'update', 'active'],
						'permissions' => ['game type upsert'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['delete'],
						'permissions' => ['game type delete'],
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

		$query = GameType::find();

		if (isset($filtering['state']) && $filtering['state'] != - 1){
			$query->andFilterWhere(['status' => $filtering['state'] ?? NULL]);
		}

		$query->andFilterCompare('name', $filtering['s'] ?? NULL, 'LIKE');

		$types = new ActiveDataProvider([
			'query'      => $query,
			'sort'       => [
				'defaultOrder' => ['ordering' => SORT_ASC]
			],
		]);

		$filters = [
			'states' => Status::states()
		];

		return $this->render('index', [
			'types'     => $types,
			'filters'   => $filters,
			'filtering' => $filtering
		]);
	}

	/**
	 * @return string|\yii\web\Response
	 */
	public function actionCreate(){
		$model = new GameType();

		if ($model->load($this->request->post()) && $model->save()){
			$this->flash('success', 'Game type successfully created.');
		}

		if (!$this->request->isAjax){
			return $this->redirect(['index']);
		}

		return $this->renderAjax('upsert', [
			'model' => $model,
		]);
	}

	/**
	 * @param $id
	 *
	 * @return string|\yii\web\Response
	 * @throws \yii\base\Exception
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function actionUpdate($id){
		$model = $this->findModel($id);

		if ($model->load($this->request->post()) && $model->save()){
			$this->flash('success', 'Game type successfully updated.');
		}

		if (!$this->request->isAjax){
			return $this->redirect(['index']);
		}

		return $this->renderAjax('upsert', [
			'model' => $model,
		]);
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

		return $this->redirect(['index']);
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
		if (!$model->isUsed()){
			if ($model->delete()){
				$this->flash('success', 'Game type has been deleted successfully');
			}
		}else{
			$this->flash('warning', 'Game type has been used, can not delete.');
		}

		return $this->back();
	}

	/**
	 * @param $id
	 *
	 * @return \modules\game\models\GameType|null
	 * @throws \yii\web\NotFoundHttpException
	 */
	protected function findModel($id){
		if (($model = GameType::findOne($id)) !== NULL){
			return $model;
		}

		throw new NotFoundHttpException('Request not found');
	}
}