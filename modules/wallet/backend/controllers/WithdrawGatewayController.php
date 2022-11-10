<?php

namespace modules\wallet\backend\controllers;

use backend\base\Controller;
use common\base\Status;
use common\base\StatusControllerBehavior;
use modules\spider\lib\sortable\SortableControllerBehavior;
use modules\wallet\models\Gateway;
use modules\wallet\models\WithdrawGateway;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AjaxFilter;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;


/**
 * Class WithdrawGatewayController
 *
 * @package modules\wallet\backend\controllers
 *
 * @method changeStatus($id, $status)
 */
class WithdrawGatewayController extends Controller{

	/**
	 * {@inheritdoc}
	 */
	public function behaviors(){
		$behaviors = [
			[
				'class' => SortableControllerBehavior::class,
				'model' => WithdrawGateway::class
			],
			[
				'class' => StatusControllerBehavior::class,
				'model' => WithdrawGateway::class
			],
			'ajax'   => [
				'class' => AjaxFilter::class,
				'only'  => ['support-coin']
			],
			'access' => [
				'rules' => [
					[
						'actions'     => ['index'],
						'allow'       => TRUE,
						'permissions' => ['wallet_withdraw_gateway']
					],
					[
						'actions'     => ['create', 'update', 'active', 'support-coin'],
						'allow'       => TRUE,
						'permissions' => ['wallet_withdraw_gateway upsert']
					],
					[
						'actions'     => ['delete'],
						'allow'       => TRUE,
						'permissions' => ['wallet_withdraw_gateway delete']
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
		if ($this->request->isAjax){
			return $this->sortNode();
		}

		$filtering = $this->request->get();
		$query     = WithdrawGateway::find()->with('customerRanks');

		if (isset($filtering['state']) && $filtering['state'] != - 1){
			$query->andFilterWhere(['status' => $filtering['state'] ?? NULL]);
		}

		$data = new ActiveDataProvider([
			'query' => $query,
			'sort'  => [
				'defaultOrder' => ['ordering' => SORT_ASC]
			],
		]);

		$filters = [
			'status' => Status::states()
		];

		return $this->render('index', [
			'data'      => $data,
			'filters'   => $filters,
			'filtering' => $filtering
		]);
	}

	/**
	 * @return string
	 * @throws \yii\db\Exception
	 */
	public function actionCreate(){
		$model = new WithdrawGateway([
			'type' => Gateway::TYPE_WITHDRAW
		]);
		if (!$model->getNewGateways()){
			$this->flash('warning', Yii::t('wallet', 'There were no available gateways.'));

			return $this->redirect(['index']);
		}

		if ($model->load($this->request->post()) && $model->save() && $model->storeRanks()){
			$this->flash('success', Yii::t('wallet', 'Gateway has been created successfully'));

			return $this->redirect(['index']);
		}

		return $this->render('create', ['model' => $model]);
	}

	/**
	 * @param $id
	 *
	 * @return string|\yii\web\Response
	 * @throws \yii\web\NotFoundHttpException|\yii\db\Exception
	 */
	public function actionUpdate($id){
		$model = $this->findModel($id);

		if ($model->load($this->request->post()) && $model->save() && $model->storeRanks()){
			$this->flash('success', Yii::t('wallet', 'Gateway has been updated successfully'));

			return $this->redirect(['index']);
		}

		$model->ranks = ArrayHelper::getColumn($model->customerRankMaps, 'customer_rank_id');

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
	 * @param $id
	 *
	 * @return \yii\web\Response
	 * @throws \Throwable
	 * @throws \yii\db\StaleObjectException
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function actionDelete($id){
		$model = $this->findModel($id);
		if ($model->delete()){
			$this->flash('success', Yii::t('wallet', 'Gateway has been deleted successfully'));
		}

		return $this->back();
	}

	/**
	 * @param $id
	 *
	 * @return WithdrawGateway|null
	 * @throws \yii\web\NotFoundHttpException
	 */
	protected function findModel($id){
		if (($model = WithdrawGateway::findOne($id)) !== NULL){
			return $model;
		}

		throw new NotFoundHttpException(Yii::t('common', 'The requested page does not exist.'));
	}

	/**
	 * @return array
	 */
	public function actionSupportCoin(){
		Yii::$app->response->format = Response::FORMAT_JSON;

		$post     = Yii::$app->request->post('depdrop_all_params');
		$selected = $this->request->get('selected');
		if (!empty($post['rate_source_id_ipt'])){
			$source_id  = $post['rate_source_id_ipt'];
			$model_temp = new WithdrawGateway(['key' => $source_id]);

			return [
				'output'   => $model_temp->gateway->supportCoins,
				'selected' => $selected
			];
		}

		return [
			'output'   => '',
			'selected' => $selected
		];
	}
}