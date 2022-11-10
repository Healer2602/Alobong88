<?php

namespace backend\controllers;

use backend\base\Controller;
use backend\models\StringTranslate;
use common\base\Status;
use common\base\StatusControllerBehavior;
use common\models\Language;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * LanguageController implements the CRUD actions for Setting model.
 */
class LanguageController extends Controller{

	/**
	 * @return array
	 */
	public function behaviors(){
		$behaviors = [
			[
				'class' => StatusControllerBehavior::class,
				'model' => Language::class
			],
			'access' => [
				'rules' => [
					[
						'allow'       => TRUE,
						'actions'     => ['index'],
						'permissions' => ['language'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['active', 'create', 'update', 'delete', 'translate'],
						'permissions' => ['language upsert'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['delete'],
						'permissions' => ['language delete'],
					]
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
		if (!isset($filtering['state'])){
			$filtering['state'] = Status::STATUS_ACTIVE;
		}

		$query = Language::find()->with('author');

		if ($filtering['state'] != - 1){
			$query->andFilterWhere(['status' => $filtering['state'] ?? NULL]);
		}

		if (!empty($filtering['s'])){
			$query->andFilterWhere(['OR', ['LIKE', 'name', $filtering['s']], ['LIKE', 'email', $filtering['s']], ['LIKE', 'username', $filtering['s']]]);
		}

		$languages = new ActiveDataProvider([
			'query'      => $query,
			'sort'       => [
				'defaultOrder' => [
					'name' => SORT_ASC
				]
			],
			'pagination' => [
				'pageSizeLimit' => [1, 100]
			]
		]);

		$filters = [
			'states' => Status::states()
		];

		return $this->render('index', [
			'languages' => $languages,
			'filters'   => $filters,
			'filtering' => $filtering
		]);
	}

	/**
	 * Creates a new Language model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 *
	 * @return mixed
	 */
	public function actionCreate(){
		$model = new Language();

		if ($model->load(Yii::$app->request->post()) && $model->save()){
			$this->flash('success', Yii::t('common', 'Language has been created successfully'));

			return $this->redirect(['update', 'id' => $model->id]);
		}

		return $this->render('create', [
			'model' => $model,
		]);
	}

	/**
	 * Updates an existing Language model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 *
	 * @param integer $id
	 *
	 * @return mixed
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	public function actionUpdate($id){
		$model = $this->findModel($id);

		if ($model->load(Yii::$app->request->post()) && $model->save()){
			$this->flash('success', Yii::t('common', 'Language has been updated successfully'));

			return $this->refresh();
		}

		return $this->render('update', [
			'model' => $model,
		]);
	}

	/**
	 * Updates an existing Language model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 *
	 * @param integer $id
	 * @param bool $force
	 *
	 * @return mixed
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	public function actionTranslate($id, $force = FALSE){
		$model     = $this->findModel($id);
		$translate = new StringTranslate([
			'language' => $model->key,
			'force'    => $force
		]);

		if ($translate->load(Yii::$app->request->post()) && $translate->upload()){
			$this->flash('success', Yii::t('common', 'Language has been updated successfully'));

			return $this->refresh();
		}

		return $this->render('update', [
			'model'     => $model,
			'translate' => $translate
		]);
	}

	/**
	 * Deletes an existing Language model.
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
		$model = $this->findModel($id);
		if (!$model->is_default){
			$model->delete();
			$this->flash('success', Yii::t('common', 'Language has been deleted successfully'));
		}

		return $this->redirect(['index']);
	}

	/**
	 * Finds the Language model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 *
	 * @param integer $id
	 *
	 * @return \common\models\Language|null the loaded model
	 * @throws \yii\web\NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($id){
		if (($model = Language::findOne($id)) !== NULL){
			return $model;
		}

		throw new NotFoundHttpException(Yii::t('common', 'Request not found'));
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
