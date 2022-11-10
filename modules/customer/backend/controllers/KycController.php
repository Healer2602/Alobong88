<?php

namespace modules\customer\backend\controllers;

use backend\base\Controller;
use common\base\StatusControllerBehavior;
use common\models\AuditTrail;
use modules\customer\models\Customer;
use modules\customer\models\Kyc;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\Response;

/**
 * Class KycController
 *
 * @package modules\customer\backend\controllers
 *
 * @method changeStatus()
 * @method softDelete()
 */
class KycController extends Controller{

	/**
	 * @return array
	 */
	public function behaviors(){

		$behaviors = [
			[
				'class' => StatusControllerBehavior::class,
				'model' => Kyc::class
			],
			'access' => [
				'rules' => [
					[
						'allow'       => TRUE,
						'actions'     => ['index'],
						'permissions' => ['customer kyc'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['detail'],
						'permissions' => ['customer kyc detail'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['approve'],
						'permissions' => ['customer kyc approve'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['reject'],
						'permissions' => ['customer kyc reject'],
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
		$query     = Kyc::find()->joinWith('customer');
		$query->andFilterWhere(['LIKE', 'customer.name', $filtering['s'] ?? NULL]);

		if (!isset($filtering['status'])){
			$filtering['status'] = Kyc::STATUS_PENDING;
		}

		if (isset($filtering['status']) && $filtering['status'] != - 1){
			$query->andFilterWhere(['kyc.status' => $filtering['status'] ?? NULL]);
		}

		$kycs = new ActiveDataProvider([
			'query' => $query
		]);

		$filters = [
			'statuses' => Kyc::listStatuses()
		];

		return $this->render('index', [
			'kycs'      => $kycs,
			'filters'   => $filters,
			'filtering' => $filtering
		]);
	}

	/**
	 * @param $id
	 *
	 * @return string|\yii\web\Response
	 */
	public function actionDetail($id){
		$model = $this->findModel($id);

		return $this->render('detail', [
			'model' => $model,
		]);
	}


	/**
	 * @param $id
	 *
	 * @return \yii\web\Response
	 */
	public function actionApprove($id){
		$model         = $this->findModel($id);
		$model->status = Kyc::STATUS_APPROVED;
		$model->detachBehavior('audit');

		if ($model->save()){
			if ($customer = $this->request->post('Customer')){
				if (!empty($customer['name'])){
					Customer::updateAll(['name' => $customer['name']],
						['id' => $model->customer_id]);
				}
			}

			$model->mailApprove();

			$message = Yii::t('customer', 'Approved eKYC: {0}',
				[$model->customer->name]);

			AuditTrail::log('eKYC', $message, Yii::t('customer', 'Player'));

			Yii::$app->getSession()
			         ->setFlash('success', Yii::t('customer', 'eKYC successfully approved'));

			return $this->redirect(['index']);
		}
	}

	/**
	 * @param $id
	 *
	 * @return string
	 */
	public function actionReject($id){
		$model                      = $this->findModel($id);
		$model->scenario            = Kyc::SCENARIO_REJECT;
		$post                       = $this->request->post();
		Yii::$app->response->format = Response::FORMAT_JSON;

		if ($model->load($post) && $model->validate()){
			$model->detachBehavior('audit');
			$model->status = Kyc::STATUS_REJECTED;

			if ($model->save()){
				$model->mailReject($model->reason);

				$message = Yii::t('customer', 'Rejected eKYC: {0} with reason: {1}',
					[$model->customer->name, $model->reason]);
				AuditTrail::log('eKYC', $message, Yii::t('customer', 'Player'));

				$this->flash('success', Yii::t('customer', 'eKYC successfully rejected'));
			}elseif ($errors = $model->errors){
				foreach ($errors as $error){
					$this->flash('error', $error);
				}
			}
		}

		if (!$this->request->isAjax){
			return $this->redirect(['index']);
		}

		return $this->renderAjax('_form_reject',
			['model' => $model]);
	}

	/**
	 * @param $id
	 *
	 * @return null|Kyc
	 */
	protected function findModel($id){
		if (($model = Kyc::findOne($id)) !== NULL){
			return $model;
		}

		return new Kyc();
	}
}