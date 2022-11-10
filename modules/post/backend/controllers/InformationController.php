<?php

namespace modules\post\backend\controllers;

use backend\base\Controller;
use common\base\Status;
use common\base\StatusControllerBehavior;
use common\models\Language;
use modules\post\backend\models\Information;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * InformationController implements the CRUD actions for News model.
 */
class InformationController extends Controller{

	/**
	 * {@inheritdoc}
	 */
	public function behaviors(){
		$behaviors = [
			[
				'class' => StatusControllerBehavior::class,
				'model' => Information::class
			],
			'access' => [
				'rules' => [
					[
						'allow'       => TRUE,
						'actions'     => ['index'],
						'permissions' => ['information'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['active', 'create', 'update'],
						'permissions' => ['information upsert'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['delete'],
						'permissions' => ['information delete'],
					],
				]
			]
		];

		return ArrayHelper::merge(parent::behaviors(), $behaviors);
	}

	/**
	 * Lists all News models.
	 *
	 * @return mixed
	 * @throws \Throwable
	 */
	public function actionIndex(){
		if ($this->request->isPost && Yii::$app->user->can('information delete')){
			return $this->batchDelete();
		}

		$filtering = $this->request->get();
		if (!isset($filtering['state'])){
			$filtering['state'] = Status::STATUS_ACTIVE;
		}

		$query = Information::find()->with('author');

		if ($filtering['state'] != - 1){
			$query->andFilterWhere(['status' => $filtering['state'] ?? NULL]);
		}

		$query->andFilterWhere(['language' => $filtering['lang'] ?? NULL]);

		if (!empty($filtering['s'])){
			$query->andFilterWhere(['OR', ['LIKE', 'name', $filtering['s']], ['LIKE', 'intro', $filtering['s']], ['LIKE', 'content', $filtering['s']]]);
		}

		$posts = new ActiveDataProvider([
			'query' => $query,
			'sort'  => [
				'defaultOrder' => ['id' => SORT_DESC]
			]
		]);

		$filters = [
			'states' => Status::states(),
			'langs'  => Language::listLanguage()
		];

		return $this->render('index', [
			'posts'     => $posts,
			'filtering' => $filtering,
			'filters'   => $filters,
		]);
	}

	/**
	 * Creates a new Information model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 *
	 * @return string|\yii\web\Response
	 */
	public function actionCreate(){
		$model           = new Information();
		$model->scenario = Information::SCENARIO_UPSERT;

		if ($model->load(Yii::$app->request->post()) && $model->save()){
			$this->flash('success', Yii::t('post', 'Information has been created successfully'));

			return $this->redirect(['index']);
		}

		return $this->render('create', [
			'model' => $model,
		]);
	}

	/**
	 * Updates an existing Information model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 *
	 * @param integer $id
	 *
	 * @return mixed
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	public function actionUpdate($id){
		$model           = $this->findModel($id);
		$model->scenario = Information::SCENARIO_UPSERT;

		if ($model->load(Yii::$app->request->post()) && $model->save()){
			$this->flash('success', Yii::t('post', 'Information has been updated successfully'));

			return $this->redirect(['index']);
		}

		return $this->render('update', [
			'model' => $model,
		]);
	}

	/**
	 * Deletes an existing Information model.
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

		return $this->redirect(['index']);
	}

	/**
	 * Finds the Information model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 *
	 * @param integer $id
	 *
	 * @return Information the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($id){
		if (($model = Information::findOne($id)) !== NULL){
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

	/**
	 * @return \yii\web\Response
	 * @throws \Throwable
	 */
	protected function batchDelete(){
		$post_ids = $this->request->post('selection', []);

		if (!empty($post_ids)){
			$total_deleted = Information::deleteAll(['id' => $post_ids]);

			if ($total_deleted){
				$this->flash('success', Yii::t('post', 'There were {0} information(s) deleted.'),
					[$total_deleted]);
			}else{
				$this->flash('error',
					Yii::t('post', 'There were no information deleted. Please try again.'),
					[$total_deleted]);
			}
		}else{
			$this->flash('warning',
				Yii::t('post', 'Please select some informations before delete.'));
		}

		return $this->refresh();
	}
}
