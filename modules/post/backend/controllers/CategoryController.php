<?php

namespace modules\post\backend\controllers;

use backend\base\Controller;
use common\base\Status;
use common\base\StatusControllerBehavior;
use common\models\Language;
use modules\post\models\Category;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * CategoryController implements the CRUD actions for PostCategory model.
 */
class CategoryController extends Controller{

	/**
	 * {@inheritdoc}
	 */
	public function behaviors(){
		$behaviors = [
			[
				'class' => StatusControllerBehavior::class,
				'model' => Category::class
			],
			'access' => [
				'rules' => [
					[
						'allow'       => TRUE,
						'actions'     => ['index'],
						'permissions' => ['news category'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['active', 'create', 'update'],
						'permissions' => ['news category upsert'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['delete'],
						'permissions' => ['news category delete'],
					],
				]
			]
		];

		return ArrayHelper::merge(parent::behaviors(), $behaviors);
	}

	/**
	 * Lists all PostCategory models.
	 *
	 * @return mixed
	 */
	public function actionIndex(){
		$filtering = $this->request->get();
		if (!isset($filtering['state'])){
			$filtering['state'] = Status::STATUS_ACTIVE;
		}

		$query = Category::find();

		if ($filtering['state'] != - 1){
			$query->andFilterWhere(['status' => $filtering['state'] ?? NULL]);
		}

		$query->andFilterWhere(['language' => $filtering['lang'] ?? NULL]);

		if (!empty($filtering['s'])){
			$query->andFilterWhere(['OR', ['LIKE', 'name', $filtering['s']], ['LIKE', 'description', $filtering['s']]]);
		}

		$categories = new ActiveDataProvider([
			'query' => $query,
		]);

		$filters = [
			'states' => Status::states(),
			'langs'  => Language::listLanguage()
		];

		return $this->render('index', [
			'categories' => $categories,
			'filtering'  => $filtering,
			'filters'    => $filters,
		]);
	}

	/**
	 * Create PostCategory model.
	 * If update is successful, the browser will be redirected to the 'update' page.
	 *
	 * @return mixed
	 */
	public function actionCreate(){
		$model = new Category();

		if ($model->load($this->request->post()) && $model->save()){
			$this->flash('success', 'Post category successfully saved');

			return $this->redirect(['update', 'id' => $model->id]);
		}

		return $this->render('create', [
			'model' => $model,
		]);
	}

	/**
	 * Updates an existing PostCategory model.
	 * If update is successful, the browser will be redirected to the 'index' page.
	 *
	 * @param integer $id
	 *
	 * @return string|\yii\web\Response
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function actionUpdate($id){
		$model = $this->findModel($id);

		if ($model->load($this->request->post()) && $model->save()){
			$this->flash('success', 'Post category successfully saved');

			return $this->refresh();
		}

		return $this->render('update', [
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

		return $this->back();
	}

	/**
	 * Deletes an existing PostCategory model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 *
	 * @param integer $id
	 *
	 * @return mixed
	 * @throws \Throwable
	 * @throws \yii\db\StaleObjectException
	 */
	public function actionDelete($id){
		$model = $this->findModel($id);
		if (!$model->posts){
			$model->delete();
			$this->flash('success', 'The category deleted successfully');
		}else{
			$this->flash('error', 'The category is assigned for some posts.');
		}

		return $this->back();
	}

	/**
	 * Finds the Category model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 *
	 * @param integer $id
	 *
	 * @return Category the loaded model
	 * @throws \yii\web\NotFoundHttpException
	 */
	protected function findModel($id){
		if (($model = Category::findOne($id)) !== NULL){
			return $model;
		}

		throw new NotFoundHttpException(Yii::t('post', 'The requested page does not exist.'));
	}
}