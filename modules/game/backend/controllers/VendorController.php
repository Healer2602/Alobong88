<?php

namespace modules\game\backend\controllers;

use backend\base\Controller;
use common\base\Status;
use common\base\StatusControllerBehavior;
use modules\game\models\Vendor;
use modules\spider\lib\sortable\SortableControllerBehavior;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * Class VendorController
 *
 * @package backend\controllers
 * @method sortNode()
 */
class VendorController extends Controller{

	/**
	 * @return array
	 */
	public function behaviors(){

		$behaviors = [
			[
				'class' => StatusControllerBehavior::class,
				'model' => Vendor::class
			],
			[
				'class' => SortableControllerBehavior::class,
				'model' => Vendor::class
			],
			'access' => [
				'rules' => [
					[
						'allow'       => TRUE,
						'actions'     => ['index'],
						'permissions' => ['game vendor'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['create', 'update', 'active'],
						'permissions' => ['game vendor upsert'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['delete'],
						'permissions' => ['game vendor delete'],
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

		$query = Vendor::find();

		if (isset($filtering['state']) && $filtering['state'] != - 1){
			$query->andFilterWhere(['status' => $filtering['state'] ?? NULL]);
		}

		$query->andFilterCompare('name', $filtering['s'] ?? NULL, 'LIKE');

		$vendors = new ActiveDataProvider([
			'query'      => $query,
			'sort'       => [
				'defaultOrder' => ['ordering' => SORT_ASC]
			],
		]);

		$filters = [
			'states' => Status::states()
		];

		return $this->render('index', [
			'vendors'   => $vendors,
			'filters'   => $filters,
			'filtering' => $filtering
		]);
	}

	/**
	 * @return string|\yii\web\Response
	 */
	public function actionCreate(){
		$model = new Vendor();

		if ($model->load($this->request->post()) && $model->save()){
			$this->flash('success', 'Vendor successfully created.');
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
			$this->flash('success', 'Vendor successfully updated.');
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
				$this->flash('success', 'Vendor has been deleted successfully');
			}
		}else{
			$this->flash('warning', 'Vendor has been used, can not delete.');
		}

		return $this->back();
	}

	/**
	 * @param $id
	 *
	 * @return \modules\game\models\Vendor|null
	 * @throws \yii\web\NotFoundHttpException
	 */
	protected function findModel($id){
		if (($model = Vendor::findOne($id)) !== NULL){
			return $model;
		}

		throw new NotFoundHttpException('Request not found');
	}
}