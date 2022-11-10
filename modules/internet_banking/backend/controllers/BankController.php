<?php

namespace modules\internet_banking\backend\controllers;

use backend\base\Controller;
use common\base\Status;
use common\base\StatusControllerBehavior;
use modules\internet_banking\models\Bank;
use Yii;
use yii\bootstrap4\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class BankController
 *
 * @package modules\internet_banking\backend\controllers
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
						'permissions' => ['internet_banking bank'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['active', 'create', 'update'],
						'permissions' => ['internet_banking bank upsert'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['delete'],
						'permissions' => ['internet_banking bank delete'],
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
		$filtering = $this->filtering();
		$query     = Bank::find()->alias('ebank')->joinWith('bank');

		if (!isset($filtering['state'])){
			$filtering['state'] = Status::STATUS_ACTIVE;
		}

		if ($filtering['state'] != - 1){
			$query->andFilterWhere(['ebank.status' => $filtering['state']]);
		}

		if (!empty($filtering['s'])){
			$query->andFilterWhere(['OR', ['LIKE', 'bank.name', $filtering['s']], ['LIKE', 'ebank.name', $filtering['s']], ['LIKE', 'ebank.code', $filtering['s']]]);
		}

		$data = new ActiveDataProvider([
			'query' => $query
		]);


		$sort                    = $data->getSort();
		$sort_attributes['name'] = [
			'asc'  => ['bank.name' => SORT_ASC],
			'desc' => ['bank.name' => SORT_DESC],
		];
		$sort->attributes        = ArrayHelper::merge($sort->attributes, $sort_attributes);
		$data->setSort($sort);

		$model   = new Bank();
		$filters = [
			'states' => $model->statuses
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
			$this->flash('success',
				Yii::t('internet_banking', 'Bank has been created successfully'));
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
			$this->flash('success',
				Yii::t('internet_banking', 'Bank has been updated successfully'));
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
		$this->flash('success', Yii::t('internet_banking', 'Bank has been deleted successfully'));

		return $this->back();
	}


	/**
	 * @param $id
	 *
	 * @return \modules\internet_banking\models\Bank|null
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
