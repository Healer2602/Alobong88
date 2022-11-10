<?php

namespace modules\wallet\backend\controllers;

use backend\base\Controller;
use modules\wallet\models\Transaction;
use modules\wallet\models\Wallet;
use modules\wallet\models\WalletSub;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;
use yii\web\NotFoundHttpException;


/**
 * Class DefaultController
 *
 * @package modules\wallet\backend\controllers
 */
class DefaultController extends Controller{

	/**
	 * {@inheritdoc}
	 */
	public function behaviors(){
		$behaviors = [
			'access' => [
				'rules' => [
					[
						'actions'     => ['index'],
						'allow'       => TRUE,
						'permissions' => ['wallet']
					],
					[
						'actions'     => ['view', 'customer'],
						'allow'       => TRUE,
						'permissions' => ['wallet detail']
					]
				],
			]
		];

		return ArrayHelper::merge(parent::behaviors(), $behaviors);
	}


	/**
	 * @return string
	 * @throws \Throwable
	 */
	public function actionIndex(){
		$filtering = $this->filtering();
		$query     = Wallet::find()
		                   ->alias('wallet')
		                   ->joinWith('customer')
		                   ->andWhere(['NOT', ['customer.id' => NULL]])
		                   ->joinWith('subWallets subwallet', FALSE)
		                   ->select(['wallet.*', 'balance_subwallet' => 'SUM(subwallet.balance)', 'balance_total' => 'SUM(subwallet.balance) + wallet.balance'])
		                   ->groupBy(['wallet.id']);

		if (!empty($filtering['s'])){
			$query->andWhere(['OR', ['LIKE', 'customer.name', $filtering['s']], ['LIKE', 'customer.email', $filtering['s']]]);
		}

		$query->andFilterWhere(['wallet.status' => $filtering['state'] ?? NULL]);

		$data = new ActiveDataProvider([
			'query' => $query,
			'sort'  => [
				'attributes' => [
					'customer_id'    => 'customer.name',
					'customer.email' => 'customer.email',
					'balance',
					'last_update',
					'status',
					'balance_subwallet',
					'balance_total',
				]
			]
		]);

		return $this->render('index', [
			'data'      => $data,
			'filtering' => $filtering,
			'statuses'  => Wallet::statuses()
		]);
	}

	/**
	 * @param $id
	 *
	 * @return string
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function actionView($id){
		$filtering = $this->request->get();
		$model     = $this->findModel($id);
		$query     = $model->getTransactions()->distinct();

		$query->andFilterWhere(['status' => $filtering['state'] ?? NULL]);
		$query->andFilterWhere(['type' => $filtering['type'] ?? NULL]);
		if (!empty($filtering['s'])){
			$query->andFilterWhere(['OR', ['LIKE', 'transaction_id', $filtering['s']], ['LIKE', 'reference_id', $filtering['s']]]);
		}
		if (!empty($filtering['date_range'])){
			$date_range_array = StringHelper::explode($filtering['date_range'], 'to');
			if (count($date_range_array) == 2){
				$query->andFilterWhere(['between', 'created_at', strtotime($date_range_array[0] ?? NULL), strtotime($date_range_array[1] ?? NULL)]);
			}else{
				$query->andFilterWhere(['created_at' => strtotime($date_range_array[0] ?? NULL)]);
			}
		}

		$activities = new ActiveDataProvider([
			'query' => $query,
			'sort'  => [
				'defaultOrder' => ['id' => SORT_DESC]
			]
		]);

		$filters = [
			'states' => Transaction::statuses(),
			'types'  => Transaction::types(),
		];

		// Sub-Wallets query
		$query = WalletSub::find()
		                  ->distinct()
		                  ->alias('ws')
		                  ->joinWith('product product_wallet')
		                  ->andWhere(['ws.wallet_id' => $id]);

		if (isset($filtering['ws_state']) && ($filtering['ws_state'] != - 1)){
			$query->andFilterWhere(['ws.status' => $filtering['ws_state'] ?? NULL]);
		}

		if (!empty($filtering['keywords'])){
			$query->andFilterWhere(['OR', ['LIKE', 'ws.game_code', $filtering['keywords']], ['LIKE', 'product_wallet.name', $filtering['keywords']]]);
		}

		$sub_wallets = new ActiveDataProvider([
			'query'      => $query,
			'pagination' => [
				'pageParam' => 'p'
			],
			'sort'       => [
				'defaultOrder' => ['last_update' => SORT_DESC]
			]
		]);

		$sort                           = $sub_wallets->getSort();
		$sort_attributes['vendor.name'] = [
			'asc'  => ['vendor.name' => SORT_ASC],
			'desc' => ['vendor.name' => SORT_DESC],
		];
		$sort->attributes               = ArrayHelper::merge($sort->attributes, $sort_attributes);
		$sub_wallets->setSort($sort);

		return $this->render('view', [
			'model'       => $model,
			'activities'  => $activities,
			'filters'     => $filters,
			'filtering'   => $filtering,
			'sub_wallets' => $sub_wallets,
		]);
	}

	/**
	 * @param $id
	 *
	 * @return \modules\wallet\models\Wallet|null
	 * @throws \yii\web\NotFoundHttpException
	 */
	private function findModel($id){
		if (($model = Wallet::findOne($id)) !== NULL){
			return $model;
		}

		throw new NotFoundHttpException(Yii::t('common', 'Request not found'));
	}

	/**
	 * @param $id
	 *
	 * @return \yii\web\Response
	 */
	public function actionCustomer($id){
		$wallet = Wallet::findOne(['customer_id' => $id]);
		if (!empty($wallet)){
			return $this->redirect(['view', 'id' => $wallet->id]);

		}

		return $this->redirect(['index']);
	}
}