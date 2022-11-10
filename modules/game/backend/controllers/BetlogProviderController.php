<?php

namespace modules\game\backend\controllers;

use backend\base\Controller;
use common\base\Status;
use common\base\StatusControllerBehavior;
use modules\game\models\BetlogProvider;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * Class BetlogProviderController
 *
 * @package backend\controllers
 */
class BetlogProviderController extends Controller{

	/**
	 * @return array
	 */
	public function behaviors(){

		$behaviors = [
			[
				'class' => StatusControllerBehavior::class,
				'model' => BetlogProvider::class
			],
			'access' => [
				'rules' => [
					[
						'allow'       => TRUE,
						'actions'     => ['index'],
						'permissions' => ['betlog_provider'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['create', 'update', 'active'],
						'permissions' => ['betlog_provider upsert'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['delete'],
						'permissions' => ['betlog_provider delete'],
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

		$query = BetlogProvider::find()->with('vendor');

		if (isset($filtering['state']) && $filtering['state'] != - 1){
			$query->andFilterWhere(['status' => $filtering['state'] ?? NULL]);
		}

		$query->andFilterCompare('code', $filtering['s'] ?? NULL, 'LIKE');

		$betlog_providers = new ActiveDataProvider([
			'query' => $query,
		]);

		$filters = [
			'states' => Status::states()
		];

		return $this->render('index', [
			'betlog_providers' => $betlog_providers,
			'filters'          => $filters,
			'filtering'        => $filtering
		]);
	}

	/**
	 * @return string|\yii\web\Response
	 */
	public function actionCreate(){
		$model = new BetlogProvider();

		if ($model->load($this->request->post()) && $model->save()){
			$this->flash('success', 'Betlog Provider successfully created.');
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
			$this->flash('success', 'Betlog Provider successfully updated.');
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
	 * @return \modules\game\models\BetlogProvider|null
	 * @throws \yii\web\NotFoundHttpException
	 */
	protected function findModel($id){
		if (($model = BetlogProvider::findOne($id)) !== NULL){
			return $model;
		}

		throw new NotFoundHttpException('Request not found');
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
		if ($model->delete()){
			$this->flash('success', 'Betlog Provider has been deleted successfully');
		}

		return $this->back();
	}
}