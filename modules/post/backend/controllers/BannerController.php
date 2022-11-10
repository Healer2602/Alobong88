<?php

namespace modules\post\backend\controllers;

use backend\base\Controller;
use common\base\Status;
use common\base\StatusControllerBehavior;
use common\models\Language;
use modules\post\backend\models\Banner;
use modules\post\models\Post;
use modules\spider\lib\sortable\SortableControllerBehavior;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * BannerController implements the CRUD actions for Banner model.
 */
class BannerController extends Controller{

	/**
	 * {@inheritdoc}
	 */
	public function behaviors(){
		$behaviors = [
			[
				'class' => StatusControllerBehavior::class,
				'model' => Banner::class
			],
			[
				'class' => SortableControllerBehavior::class,
				'model' => Banner::class
			],
			'access' => [
				'rules' => [
					[
						'allow'       => TRUE,
						'actions'     => ['index'],
						'permissions' => ['banner'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['active', 'create', 'update'],
						'permissions' => ['banner upsert'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['delete'],
						'permissions' => ['banner delete'],
					],
				]
			]
		];

		return ArrayHelper::merge(parent::behaviors(), $behaviors);
	}

	/**
	 * Lists all Banner models.
	 *
	 * @return mixed
	 * @throws \Throwable
	 */
	public function actionIndex(){
		if ($this->request->isAjax){
			return $this->sortNode();
		}

		$filtering = $this->request->get();
		if (!isset($filtering['state'])){
			$filtering['state'] = Status::STATUS_ACTIVE;
		}

		$query = Banner::find()->with('author')->orderBy(['ordering' => SORT_ASC]);

		if ($filtering['state'] != - 1){
			$query->andFilterWhere(['status' => $filtering['state'] ?? NULL]);
		}

		if (!empty($filtering['s'])){
			$query->andFilterWhere(['LIKE', 'name', $filtering['s']]);
		}

		$query->andFilterWhere(['language' => $filtering['lang'] ?? NULL]);

		$posts = new ActiveDataProvider([
			'query'      => $query,
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
	 * Creates a new Banner model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 *
	 * @return mixed
	 */
	public function actionCreate(){
		$model           = new Banner();
		$model->scenario = Post::SCENARIO_UPSERT;

		if ($model->load(Yii::$app->request->post()) && $model->save()){
			$this->flash('success', Yii::t('post', 'Banner successfully created'));

			return $this->redirect(['index']);
		}

		return $this->render('create', [
			'model' => $model,
		]);
	}

	/**
	 * Updates an existing Banner model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 *
	 * @param integer $id
	 *
	 * @return mixed
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	public function actionUpdate($id){
		$model           = $this->findModel($id);
		$model->scenario = Banner::SCENARIO_UPSERT;

		if ($model->load(Yii::$app->request->post()) && $model->save()){
			$this->flash('success', Yii::t('post', 'Banner has been updated successfully'));

			return $this->redirect(['index']);
		}

		return $this->render('update', [
			'model' => $model,
		]);
	}

	/**
	 * Deletes an existing Banner model.
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
		if ($this->findModel($id)->delete()){
			$this->flash('success', Yii::t('post', 'Banner has been deleted successfully'));
		}

		return $this->redirect(['index']);
	}

	/**
	 * Finds the Banner model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 *
	 * @param integer $id
	 *
	 * @return Banner the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($id){
		if (($model = Banner::findOne($id)) !== NULL){
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
