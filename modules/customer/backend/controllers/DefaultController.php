<?php

namespace modules\customer\backend\controllers;

use backend\base\Controller;
use common\base\StatusControllerBehavior;
use modules\customer\models\Customer;
use modules\customer\models\CustomerBank;
use modules\customer\models\CustomerRank;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * Class CustomerController
 *
 * @package backend\controllers
 */
class DefaultController extends Controller{

	/**
	 * @return array
	 */
	public function behaviors(){
		$behaviors = [
			[
				'class' => StatusControllerBehavior::class,
				'model' => Customer::class
			],
			'access' => [
				'rules' => [
					[
						'allow'       => TRUE,
						'actions'     => ['index'],
						'permissions' => ['customer'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['create', 'update', 'validate', 'active'],
						'permissions' => ['customer upsert'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['delete'],
						'permissions' => ['customer delete'],
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

		$query = Customer::find()->notDeleted()
		                 ->joinWith('rank')
		                 ->with('kyc', 'referralCustomer', 'agent');

		if (isset($filtering['state']) && $filtering['state'] != - 1){
			$query->andFilterWhere(['customer.status' => $filtering['state'] ?? NULL]);
		}

		$query->andFilterWhere(['rank.id' => $filtering['rank'] ?? NULL]);
		if (!empty($filtering['s'])){
			$query->andFilterWhere(['OR',
				['LIKE', 'customer.name', $filtering['s']],
				['LIKE', 'customer.email', $filtering['s']],
				['LIKE', 'customer.username', $filtering['s']]
			]);
		}

		$customers = new ActiveDataProvider([
			'query' => $query,
			'sort'  => [
				'defaultOrder' => [
					'created_at' => SORT_DESC
				]
			],
		]);

		$filters = [
			'ranks'  => CustomerRank::findList(),
			'states' => (new Customer())->getStatuses()
		];

		return $this->render('index', [
			'customers' => $customers,
			'filters'   => $filters,
			'filtering' => $filtering
		]);
	}

	/**
	 * @return string|\yii\web\Response
	 */
	public function actionCreate(){
		$model           = new Customer();
		$model->scenario = Customer::SCENARIO_CREATE;

		if ($model->load($this->request->post()) && $model->save()){
			$this->flash('success', 'Player successfully created.');

			return $this->redirect(['update', 'id' => $model->id]);
		}

		return $this->render('create', [
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
		$query = CustomerBank::find()->andWhere(['customer_id' => $id]);

		$details = new ActiveDataProvider([
			'query'      => $query,
			'sort'       => [
				'defaultOrder' => [
					'id' => SORT_DESC
				]
			],
			'pagination' => [
				'pageSize' => - 1,
			],
		]);

		if ($model->load($this->request->post()) && $model->save()){
			$this->flash('success', 'Player successfully updated.');

			return $this->redirect(['index']);
		}

		return $this->render('update', [
			'model'   => $model,
			'details' => $details
		]);
	}

	/**
	 * @param $id
	 *
	 * @return mixed
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function actionValidate($id = 0){
		if (!empty($id)){
			$model = $this->findModel($id);
		}else{
			$model           = new Customer();
			$model->scenario = Customer::SCENARIO_CREATE;
		}

		if ($this->request->isAjax && $model->load($this->request->post())){
			Yii::$app->response->format = Response::FORMAT_JSON;

			return ActiveForm::validate($model);
		}

		return [];
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
	 * @throws \yii\db\StaleObjectException
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function actionDelete($id){
		$model = $this->findModel($id);

		if ($model->referral){
			$this->flash('error', 'Cannot delete an referral');
		}else{

			if ($kyc = $model->kyc){
				$kyc->delete();
			}

			if (($wallet = $model->wallet) && empty($model->wallet->balance) && empty($model->wallet->transactions)){
				$wallet->delete();
			}

			$model->delete();
			$this->flash('success', 'Player has been deleted successfully');
		}

		return $this->back();
	}

	/**
	 * @param $id
	 *
	 * @return null|Customer
	 * @throws \yii\web\NotFoundHttpException
	 */
	protected function findModel($id){
		if (($model = Customer::findOne($id)) !== NULL){
			return $model;
		}

		throw new NotFoundHttpException('Request not found');
	}
}