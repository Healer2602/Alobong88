<?php

namespace modules\block\backend\controllers;

use backend\base\Controller;
use common\base\StatusControllerBehavior;
use modules\block\backend\models\BlockModel;
use modules\block\backend\models\BlockSearch;
use modules\block\models\Block;
use modules\spider\lib\sortable\SortableControllerBehavior;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * BlockController implements the CRUD actions for Block model.
 * @method sortNode()
 */
class DefaultController extends Controller{

	/**
	 * {@inheritdoc}
	 */
	public function behaviors(){
		$behaviors = [
			[
				'class' => StatusControllerBehavior::class,
				'model' => Block::class
			],
			[
				'class' => SortableControllerBehavior::class,
				'model' => Block::class
			],
			'access' => [
				'rules' => [
					[
						'allow'       => TRUE,
						'actions'     => ['index'],
						'permissions' => ['block'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['active', 'create', 'update'],
						'permissions' => ['block upsert'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['delete'],
						'permissions' => ['block delete'],
					],
				]
			]
		];

		return ArrayHelper::merge(parent::behaviors(), $behaviors);
	}

	/**
	 * Lists all Block models.
	 *
	 * @return mixed
	 */
	public function actionIndex(){
		if ($this->request->isAjax){
			return $this->sortNode();
		}

		$searchModel  = new BlockSearch();
		$dataProvider = $searchModel->search(Yii::$app->request->queryParams);

		return $this->render('index', [
			'searchModel'  => $searchModel,
			'dataProvider' => $dataProvider
		]);
	}

	/**
	 * Creates a new Block model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 *
	 * @param string $type
	 *
	 * @return mixed
	 */
	public function actionCreate($type = 'html'){
		$model = new BlockModel([
			'type' => $type
		]);

		if ($model->load(Yii::$app->request->post()) && $model->save()){
			$this->flash('success', 'Block has been created successfully');

			return $this->redirect(['update', 'id' => $model->id]);
		}

		return $this->render('create', [
			'model' => $model
		]);
	}

	/**
	 * Updates an existing Block model.
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
			$this->flash('success', 'Block has been updated successfully');

			TagDependency::invalidate(Yii::$app->cache, "block-{$model->id}");

			return $this->redirect(['index']);
		}

		return $this->render('update', [
			'model' => $model,
		]);
	}

	/**
	 * Deletes an existing Block model.
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
		$this->flash('success', 'Block has been deleted successfully');

		return $this->back();
	}

	/**
	 * Finds the Block model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 *
	 * @param integer $id
	 *
	 * @return BlockModel the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($id){
		if (($model = BlockModel::findOne($id)) !== NULL){
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
		TagDependency::invalidate(Yii::$app->cache, "block-{$model->id}");

		return $this->back();
	}
}