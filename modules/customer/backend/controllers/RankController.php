<?php

namespace modules\customer\backend\controllers;

use backend\base\Controller;
use common\base\Status;
use common\base\StatusControllerBehavior;
use modules\customer\models\CustomerRank;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * Class RankController
 *
 * @package modules\customer\backend\controllers
 *
 * @method changeStatus()
 * @method softDelete()
 */
class RankController extends Controller{

	/**
	 * @return array
	 */
	public function behaviors(){

		$behaviors = [
			[
				'class' => StatusControllerBehavior::class,
				'model' => CustomerRank::class
			],
			'access' => [
				'rules' => [
					[
						'allow'       => TRUE,
						'actions'     => ['index'],
						'permissions' => ['customer rank'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['active', 'create', 'update'],
						'permissions' => ['customer rank upsert'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['delete'],
						'permissions' => ['customer rank delete'],
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

		$query = CustomerRank::find();
		$query->andFilterWhere(['LIKE', 'name', $filtering['s'] ?? NULL]);

		if (isset($filtering['state']) && $filtering['state'] != - 1){
			$query->andFilterWhere(['status' => $filtering['state'] ?? NULL]);
		}

		$ranks = new ActiveDataProvider([
			'query' => $query
		]);

		$filters = [
			'statuses' => Status::states()
		];

		return $this->render('index', [
			'ranks'     => $ranks,
			'filters'   => $filters,
			'filtering' => $filtering
		]);
	}

	/**
	 * @param $id
	 *
	 * @return string|\yii\web\Response
	 */
	public function actionCreate($id = 0){
		$model = $this->findModel($id);

		if ($post = $this->request->post()){
			if ($model->load($post) && $model->save()){
				$this->flash('success', 'Player rank successfully saved.');
			}elseif ($errors = $model->errors){
				foreach ($errors as $error){
					$this->flash('error', $error);
				}
			}
		}

		if (!$this->request->isAjax){
			return $this->back();
		}

		return $this->renderAjax('_form', [
			'model' => $model,
		]);
	}

	/**
	 * @param $id
	 *
	 * @return \yii\web\Response
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
	 */
	public function actionDelete($id){
		$model = $this->findModel($id);
		$this->softDelete($model->id);

		return $this->redirect(['index']);
	}

	/**
	 * @param $id
	 *
	 * @return null|CustomerRank
	 */
	protected function findModel($id){
		if (($model = CustomerRank::findOne($id)) !== NULL){
			return $model;
		}

		return new CustomerRank();
	}
}