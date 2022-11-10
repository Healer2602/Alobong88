<?php

namespace modules\game\backend\controllers;

use backend\base\Controller;
use common\base\Status;
use common\base\StatusControllerBehavior;
use modules\game\models\ProductWallet;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * Class ProductWalletController
 *
 * @package backend\controllers
 */
class ProductWalletController extends Controller{

	/**
	 * @return array
	 */
	public function behaviors(){

		$behaviors = [
			[
				'class' => StatusControllerBehavior::class,
				'model' => ProductWallet::class
			],
			'access' => [
				'rules' => [
					[
						'allow'       => TRUE,
						'actions'     => ['index'],
						'permissions' => ['product_wallet'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['create', 'update', 'active'],
						'permissions' => ['product_wallet upsert'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['delete'],
						'permissions' => ['product_wallet delete'],
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

		$query = ProductWallet::find()->with('type', 'vendor');

		if (isset($filtering['state']) && $filtering['state'] != - 1){
			$query->andFilterWhere(['status' => $filtering['state'] ?? NULL]);
		}

		$query->andFilterCompare('name', $filtering['s'] ?? NULL, 'LIKE');

		$vendors = new ActiveDataProvider([
			'query' => $query,
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
		$model = new ProductWallet();

		if ($model->load($this->request->post()) && $model->save()){
			$this->flash('success', 'Product Wallet successfully created.');
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
			$this->flash('success', 'Product Wallet successfully updated.');
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
		if ($model->delete()){
			$this->flash('success', 'Product Wallet has been deleted successfully');
		}

		return $this->back();
	}

	/**
	 * @param $id
	 *
	 * @return \modules\game\models\ProductWallet|null
	 * @throws \yii\web\NotFoundHttpException
	 */
	protected function findModel($id){
		if (($model = ProductWallet::findOne($id)) !== NULL){
			return $model;
		}

		throw new NotFoundHttpException('Request not found');
	}
}