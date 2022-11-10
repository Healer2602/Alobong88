<?php

namespace modules\wallet\backend\controllers;

use backend\base\Controller;
use common\base\Status;
use common\base\StatusControllerBehavior;
use modules\wallet\models\Bank;
use Yii;
use yii\bootstrap5\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class BankController
 *
 * @package modules\wallet\backend\controllers
 */
class BankController extends Controller{

	/**
	 * {@inheritdoc}
	 */
	public function behaviors(){
		$behaviors = [
			[
				'class' => StatusControllerBehavior::class,
				'model' => Bank::class
			],
			'access' => [
				'rules' => [
					[
						'allow'       => TRUE,
						'actions'     => ['index'],
						'permissions' => ['wallet bank'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['active', 'create', 'update'],
						'permissions' => ['wallet bank upsert'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['delete'],
						'permissions' => ['wallet bank delete'],
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
		$query     = Bank::find();

		if (!isset($filtering['state'])){
			$filtering['state'] = Status::STATUS_ACTIVE;
		}

		if ($filtering['state'] != - 1){
			$query->andFilterWhere(['status' => $filtering['state']]);
		}

		if (!empty($filtering['s'])){
			$query->andFilterWhere(['LIKE', 'name', $filtering['s']]);
		}

		$data = new ActiveDataProvider([
			'query' => $query,
			'sort'  => [
				'defaultOrder' => [
					'name' => SORT_ASC
				]
			]
		]);

		$model   = new Bank();
		$filters = [
			'states' => $model->statuses,
		];

		return $this->render('index', [
			'data'      => $data,
			'filtering' => $filtering,
			'filters'   => $filters
		]);
	}


	/**
	 * @return \yii\web\Response|array|string
	 */
	public function actionCreate(){
		$model = new Bank();

		if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())){
			Yii::$app->response->format = Response::FORMAT_JSON;

			return ActiveForm::validate($model);
		}elseif ($model->load(Yii::$app->request->post()) && $model->save()){
			$this->flash('success', Yii::t('wallet', 'Bank has been created successfully'));
		}

		if (!Yii::$app->request->isAjax){
			return $this->back();
		}

		return $this->renderAjax('create', [
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

		if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())){
			Yii::$app->response->format = Response::FORMAT_JSON;

			return ActiveForm::validate($model);
		}elseif ($model->load(Yii::$app->request->post()) && $model->save()){
			$this->flash('success', Yii::t('wallet', 'Bank has been updated successfully'));
		}

		if (!Yii::$app->request->isAjax){
			return $this->back();
		}

		return $this->renderAjax('update', [
			'model' => $model,
		]);
	}


	/**
	 * @param $id
	 *
	 * @return \yii\web\Response
	 * @throws \Throwable
	 * @throws \yii\db\StaleObjectException
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function actionDelete($id){
		$this->findModel($id)->delete();
		$this->flash('success', Yii::t('wallet', 'Bank has been deleted successfully'));

		return $this->back();
	}


	/**
	 * @param $id
	 *
	 * @return \modules\wallet\models\Bank|null
	 * @throws \yii\web\NotFoundHttpException
	 */
	protected function findModel($id){
		if (($model = Bank::findOne($id)) !== NULL){
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
