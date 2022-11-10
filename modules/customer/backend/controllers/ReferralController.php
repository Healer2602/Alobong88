<?php

namespace modules\customer\backend\controllers;

use backend\base\Controller;
use common\base\Status;
use modules\customer\models\AssignCustomer;
use modules\customer\models\Referral;
use modules\wallet\models\Wallet;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * ReferralController implements the CRUD actions for referral model.
 */
class ReferralController extends Controller{

	/**
	 * {@inheritdoc}
	 */
	public function behaviors(){
		$behaviors = [
			'access' => [
				'rules' => [
					[
						'allow'       => TRUE,
						'permissions' => ['customer referral'],
					],
				]
			]
		];

		return ArrayHelper::merge(parent::behaviors(), $behaviors);
	}

	/**
	 * Lists all Referral models.
	 *
	 * @return mixed
	 */
	public function actionIndex(){
		$filtering = $this->request->get();
		$query     = Referral::find()
		                     ->distinct()
		                     ->joinWith('customer')
		                     ->joinWith('assignedCustomers assign', FALSE)
		                     ->select(['referral.*', 'total' => 'COUNT(assign.id)'])
		                     ->groupBy(['referral.id']);

		if (isset($filtering['state']) && $filtering['state'] != - 1){
			$query->andFilterWhere(['status' => $filtering['state'] ?? NULL]);
		}

		if (!empty($filtering['s'])){
			$query->andFilterWhere(['OR', ['LIKE', 'customer.name', $filtering['s']], ['LIKE', 'customer.email', $filtering['s']]]);
		}

		$referrals = new ActiveDataProvider([
			'query' => $query
		]);

		$filters['states'] = Status::states();

		return $this->render('index', [
			'referrals' => $referrals,
			'filters'   => $filters,
			'filtering' => $filtering
		]);
	}

	/**
	 * Updates an existing Referral model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 *
	 * @param integer $id
	 *
	 * @return mixed
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	public function actionView($id){
		$model = $this->findModel($id);

		if ($model->load(Yii::$app->request->post()) && $model->save()){
			$this->flash('success', 'Referral has been updated successfully');

			return $this->refresh();
		}

		$assign = new AssignCustomer([
			'referral_id' => $id
		]);

		if ($assign->load(Yii::$app->request->post()) && $assign->assign()){
			$this->flash('success', 'Player has been assigned successfully');

			return $this->refresh();
		}

		$query = Wallet::find()
		               ->alias('wallet')
		               ->joinWith('customer')
		               ->andWhere(['NOT', ['customer.id' => NULL]])
		               ->andWhere(['wallet.status' => Wallet::STATUS_ACTIVE])
		               ->andWhere(['customer.referral_id' => $id]);

		if ($s = $this->request->get('s')){
			$query->andWhere(['OR', ['LIKE', 'customer.name', $s], ['LIKE', 'customer.email', $s]]);
		}

		$data = new ActiveDataProvider([
			'query'      => $query,
			'sort'       => [
				'attributes' => [
					'customer_id'    => 'customer.name',
					'customer.email' => 'customer.email',
					'balance',
					'last_update',
					'status'
				]
			]
		]);

		return $this->render('view', [
			'model'     => $model,
			'assign'    => $assign,
			'customers' => $data
		]);
	}

	/**
	 * Finds the Referral model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 *
	 * @param integer $id
	 *
	 * @return Referral the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($id){
		if (($model = Referral::findOne($id)) !== NULL){
			return $model;
		}

		throw new NotFoundHttpException(Yii::t('common', 'The requested page does not exist.'));
	}
}
