<?php

namespace modules\wallet\backend\controllers;

use backend\base\Controller;
use common\base\AppHelper;
use common\models\AuditTrail;
use Exception;
use modules\wallet\backend\models\UpdateTransaction;
use modules\wallet\models\Notification;
use modules\wallet\models\Transaction;
use modules\wallet\models\Wallet;
use modules\wallet\models\WalletSubTransaction;
use Throwable;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;


/**
 * Class TransactionController
 *
 * @package modules\wallet\backend\controllers
 */
class TransactionController extends Controller{

	/**
	 * {@inheritdoc}
	 */
	public function behaviors(){
		$behaviors = [
			'verbs'  => [
				'actions' => [
					'approve'  => ['POST'],
					'reject'   => ['POST'],
					'operator' => ['POST'],
					'update'   => ['POST'],
				]
			],
			'access' => [
				'rules' => [
					[
						'actions'     => ['index'],
						'allow'       => TRUE,
						'permissions' => ['wallet transaction']
					],
					[
						'actions'     => ['view'],
						'allow'       => TRUE,
						'permissions' => ['wallet transaction detail']
					],
					[
						'actions'     => ['withdrawal'],
						'allow'       => TRUE,
						'permissions' => ['wallet withdrawal']
					],
					[
						'actions'     => ['operator'],
						'allow'       => TRUE,
						'permissions' => ['wallet transaction approve', 'wallet transaction return']
					],
					[
						'actions'     => ['update'],
						'allow'       => TRUE,
						'permissions' => ['wallet withdrawal update']
					],
					[
						'actions'     => ['refund-transfer'],
						'allow'       => TRUE,
						'permissions' => ['wallet withdrawal return']
					],
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
		Yii::$app->user->setReturnUrl($this->request->getAbsoluteUrl());

		$filtering = $this->request->get();

		if (empty($filtering['wallet']) || $filtering['wallet'] == Wallet::MAIN_WALLET){
			$query = Transaction::find();
		}else{
			$query = WalletSubTransaction::find()
			                             ->joinWith('subWallet.product product', FALSE)
			                             ->andWhere(['product.code' => $filtering['wallet']])
			                             ->andFilterWhere(['transaction.wallet_sub_id' => $filtering['id'] ?? NULL]);
		}

		$query->alias('transaction')
		      ->joinWith('wallet')
		      ->joinWith('wallet.customer customer')
		      ->distinct();

		$query->andFilterWhere(['transaction.status' => $filtering['state'] ?? NULL]);
		$query->andFilterWhere(['transaction.type' => $filtering['type'] ?? NULL]);
		$query->andFilterWhere(['customer.id' => $filtering['customer'] ?? NULL]);
		if (!empty($filtering['s'])){
			$query->andFilterWhere(['OR', ['LIKE', 'transaction_id', $filtering['s']], ['LIKE', 'reference_id', $filtering['s']]]);
		}
		if (!empty($filtering['date_range'])){
			$range = AppHelper::parseDateRange($filtering['date_range']);
			if (!empty($range[0])){
				$query->andFilterWhere(['>=', 'transaction.created_at', $range[0]]);
			}
			if (!empty($range[1])){
				$query->andFilterWhere(['<=', 'transaction.created_at', $range[1]]);
			}
		}

		$data = new ActiveDataProvider([
			'query' => $query,
			'sort'  => [
				'defaultOrder' => ['id' => SORT_DESC]
			]
		]);

		$filters = [
			'states'    => Transaction::statuses(),
			'types'     => Transaction::types(),
			'customers' => Transaction::customers(),
			'wallets'   => Transaction::wallets()
		];

		return $this->render('index', [
			'data'      => $data,
			'filters'   => $filters,
			'filtering' => $filtering
		]);
	}

	/**
	 * @param $id
	 * @param null $wallet
	 *
	 * @return string|\yii\web\Response
	 * @throws \yii\web\NotFoundHttpException
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function actionView($id, $wallet = NULL){
		Yii::$app->user->setReturnUrl($this->request->getAbsoluteUrl());

		$model = $this->findModel($id, $wallet);

		if (!$this->request->isAjax){
			return $this->back();
		}

		return $this->renderAjax('view', [
			'model' => $model
		]);
	}

	/**
	 * @param $id
	 * @param null $wallet
	 *
	 * @return Transaction|WalletSubTransaction|null
	 * @throws \yii\web\NotFoundHttpException
	 */
	private function findModel($id, $wallet = NULL){
		if (empty($wallet) || $wallet == Wallet::MAIN_WALLET){
			$model = Transaction::findOne($id);
		}else{
			$model = WalletSubTransaction::findOne($id);
		}

		if (!empty($model)){
			return $model;
		}

		throw new NotFoundHttpException(Yii::t('common', 'Request not found'));
	}

	/**
	 * @return string
	 */
	public function actionWithdrawal(){
		Yii::$app->user->setReturnUrl($this->request->getAbsoluteUrl());

		$filtering = $this->request->get();
		$query     = Transaction::find()
		                        ->alias('transaction')
		                        ->joinWith('wallet wallet')
		                        ->joinWith('wallet.customer customer')
		                        ->andWhere(['type' => Transaction::TYPE_WITHDRAW])
		                        ->andWhere(['transaction.status' => Transaction::STATUS_PENDING])
		                        ->andWhere(['wallet.status' => Wallet::STATUS_ACTIVE]);

		$query->andFilterWhere(['customer.id' => $filtering['customer'] ?? NULL]);
		if (!empty($filtering['s'])){
			$query->andFilterWhere(['OR', ['LIKE', 'transaction_id', $filtering['s']], ['LIKE', 'reference_id', $filtering['s']]]);
		}
		if (!empty($filtering['date_range'])){
			$range = AppHelper::parseDateRange($filtering['date_range']);
			if (!empty($range[0])){
				$query->andFilterWhere(['>=', 'transaction.created_at', $range[0]]);
			}
			if (!empty($range[1])){
				$query->andFilterWhere(['<=', 'transaction.created_at', $range[1]]);
			}
		}

		$data = new ActiveDataProvider([
			'query' => $query,
			'sort'  => [
				'defaultOrder' => ['id' => SORT_DESC]
			]
		]);

		$filters = [
			'customers' => Transaction::customers()
		];

		return $this->render('withdrawal', [
			'data'      => $data,
			'filters'   => $filters,
			'filtering' => $filtering
		]);
	}

	/**
	 * @param $id
	 * @param $action
	 *
	 * @return string
	 * @throws \yii\web\NotFoundHttpException|\yii\base\InvalidConfigException
	 */
	public function actionOperator($id, $action){
		$model           = $this->findModel($id);
		$model->scenario = $model::SCENARIO_OPERATOR;

		if ($model->load($this->request->post()) && $model->save()){
			if ($action == 'refund'){
				return $this->actionReturn($model);
			}

			if ($action == 'approve'){
				return $this->actionApprove($model);
			}

			if ($action == 'reject'){
				return $this->actionReject($model);
			}
		}

		if (!$this->request->isAjax){
			return $this->goBack(['index']);
		}

		return $this->renderAjax('operator', [
			'model' => $model
		]);
	}

	/**
	 * @param Transaction $model
	 *
	 * @return \yii\web\Response
	 * @throws \yii\base\InvalidConfigException
	 */
	private function actionApprove($model){
		if ($model->needApproval()){
			$params = $model->formatParams();
			if ($model->type == Transaction::TYPE_WITHDRAW){
				$gateway = $model->gateway;
				if (!empty($gateway)){
					$withdraw = $gateway->gateway->withdraw($model->amount, $model->currency,
						$model, TRUE);

					if ($withdraw){
						$model->status = Transaction::STATUS_SUCCESS;

						if ($model->save(FALSE)){
							$this->flash('success',
								'Withdrawal request has been approved successfully.');

							AuditTrail::log('Approval',
								Yii::t('wallet', 'Approved withdrawal request: {0}',
									[$model->transaction_id]), 'eWallet');

							Notification::withdrawApproved($model);
						}
					}else{
						$this->flash('error',
							'Withdrawal request approval failed. Please try again.');
					}
				}
			}elseif ($model->type == Transaction::TYPE_TOPUP){
				$model->status  = Transaction::STATUS_SUCCESS;
				$db_transaction = Yii::$app->db->beginTransaction();
				try{
					if ($model->save(FALSE)){
						$wallet = $model->wallet;
						if (!empty($wallet) && $wallet->verify()){
							$wallet->balance += abs($model->amount);
							if ($wallet->save(FALSE)){
								$db_transaction->commit();

								AuditTrail::log('Approval',
									Yii::t('wallet', 'Approve deposit request: {0}',
										[$model->transaction_id]), 'eWallet');

								$this->flash('success',
									'Deposit request has been approved successfully.');
							}
						}else{
							$wallet->setAsFraud();
							$db_transaction->commit();

							$this->flash('error',
								'Player wallet has been detected as fraud.');
						}
					}
				}catch (Exception $exception){
					$db_transaction->rollBack();
				}catch (Throwable $exception){
					$db_transaction->rollBack();
				}
			}
		}

		return $this->goBack(['index']);
	}

	/**
	 * @param Transaction $model
	 *
	 * @return \yii\web\Response
	 */
	private function actionReject($model){
		if ($model->needApproval()){
			$model->status  = Transaction::STATUS_REJECTED;
			$db_transaction = Yii::$app->db->beginTransaction();
			try{
				if ($model->save(FALSE)){
					$wallet = $model->wallet;
					if (!empty($wallet) && $wallet->verify()){
						if ($model->type == Transaction::TYPE_WITHDRAW){
							$wallet->balance += abs($model->amount);
							if ($wallet->save(FALSE)){
								$transaction = new Transaction([
									'amount'       => abs($model->amount),
									'status'       => Transaction::STATUS_SUCCESS,
									'wallet_id'    => $wallet->id,
									'type'         => Transaction::TYPE_RETURN,
									'description'  => Yii::t('wallet',
										"Return from withdrawal: {0}",
										[$model->transaction_id]),
									'reference_id' => $model->transaction_id
								]);

								$transaction->save();

								$db_transaction->commit();

								Notification::withdrawRejected($model);

								AuditTrail::log('Reject',
									Yii::t('wallet', 'Rejected withdrawal request: {0}',
										[$model->transaction_id]), 'eWallet');

								$this->flash('success',
									'Withdrawal request has been rejected successfully.');
							}
						}else{
							$db_transaction->commit();

							AuditTrail::log('Reject',
								Yii::t('wallet', 'Rejected deposit request: {0}',
									[$model->transaction_id]), 'eWallet');

							$this->flash('success',
								'Deposit request has been rejected successfully.');
						}
					}else{
						$wallet->setAsFraud();
						$db_transaction->commit();

						$this->flash('error',
							'Player wallet has been detected as fraud.');
					}
				}
			}catch (Exception $exception){
				$db_transaction->rollBack();
			}catch (Throwable $exception){
				$db_transaction->rollBack();
			}

			if (!empty($exception)){
				$this->flash('error',
					'Cannot reject the transaction right now. Please try again.');
			}
		}

		return $this->goBack(['index']);
	}

	/**
	 * @param Transaction $model
	 *
	 * @return \yii\web\Response
	 * @throws \yii\base\InvalidConfigException
	 */
	private function actionReturn($model){
		if ($model->canReturn()){
			$model->status      = Transaction::STATUS_CANCELED;
			$model->description = Yii::t('wallet', 'Refund at {0} by {1}',
				[Yii::$app->formatter->asDatetime(time()), Yii::$app->user->identity->name]);

			$db_transaction = Yii::$app->db->beginTransaction();
			try{
				if ($model->save(FALSE)){
					$wallet = $model->wallet;
					if (!empty($wallet) && $wallet->verify()){
						$wallet->balance += abs($model->amount);
						if ($wallet->save(FALSE)){
							$transaction = new Transaction([
								'amount'       => abs($model->amount),
								'status'       => Transaction::STATUS_SUCCESS,
								'wallet_id'    => $wallet->id,
								'type'         => Transaction::TYPE_RETURN,
								'description'  => Yii::t('wallet', "Return from withdrawal: {0}",
									[$model->transaction_id]),
								'reference_id' => $model->transaction_id
							]);

							$transaction->save();

							$db_transaction->commit();

							AuditTrail::log('Refund',
								Yii::t('wallet', 'Refund for withdrawal request: {0}',
									[$model->transaction_id]), 'eWallet');

							$this->flash('success',
								'Withdrawal request has been refunded successfully.');
						}
					}else{
						$wallet->setAsFraud();
						$db_transaction->commit();

						$this->flash('error',
							'Player wallet has been detected as fraud.');
					}
				}
			}catch (Exception $exception){
				$db_transaction->rollBack();
			}catch (Throwable $exception){
				$db_transaction->rollBack();
			}

			if (!empty($exception)){
				$this->flash('error',
					'Cannot refunded the withdrawal request right now. Please try again.');
			}
		}

		return $this->goBack(['index']);
	}

	/**
	 * @param $id
	 *
	 * @return string
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function actionUpdate($id){
		$model = $this->findModel($id);
		if ($model->canUpdateStatus()){
			$params = $model->formatParams();
			$model  = new UpdateTransaction([
				'transaction_id' => $model->transaction_id,
				'wallet_id'      => $model->wallet_id,
				'gateway'        => $params['Gateway ID'] ?? NULL,
				'amount'         => $params['receive_amount'] ?? 0,
				'currency'       => $params['receive_currency'] ?? Yii::$app->formatter->currencyCode
			]);

			if ($model->load($this->request->post()) && $model->save()){
				$this->flash('success', 'Deposit request has been updated successfully.');
			}

			if (!$this->request->isAjax){
				return $this->goBack(['index']);
			}

			return $this->renderAjax('update', [
				'model' => $model
			]);
		}

		return $this->goBack(['index']);
	}

	/**
	 * @param $id
	 * @param $wallet
	 *
	 * @return string|\yii\web\Response
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function actionRefundTransfer($id, $wallet = NULL){
		$model           = $this->findModel($id, $wallet);
		$model->scenario = $model::SCENARIO_OPERATOR;

		if ($model->load($this->request->post()) && $model->save() && $model->canReturnTransfer()){
			$model->description = Yii::t('wallet', 'Refund at {0} by {1}',
				[Yii::$app->formatter->asDatetime(time()), Yii::$app->user->identity->name]);

			$db_transaction = Yii::$app->db->beginTransaction();
			try{
				if ($model->save(FALSE)){
					if (empty($wallet)){
						$wallet_model = $model->wallet;
					}else{
						$wallet_model = $model->wallet->wallet ?? NULL;
					}

					if (!empty($wallet_model) && $wallet_model->verify()){
						$wallet_model->balance += abs($model->amount);
						if ($wallet_model->save(FALSE)){
							$transaction = new Transaction([
								'amount'       => abs($model->amount),
								'status'       => Transaction::STATUS_SUCCESS,
								'wallet_id'    => $wallet_model->id,
								'type'         => Transaction::TYPE_RETURN,
								'description'  => Yii::t('wallet',
									"Refund from transfer: {0}",
									[$model->transaction_id]),
								'reference_id' => $model->transaction_id
							]);

							$transaction->save();

							$db_transaction->commit();

							AuditTrail::log('Refund',
								Yii::t('wallet', 'Refund for transfer: {0}',
									[$model->transaction_id]), 'eWallet');

							$this->flash('success',
								'Transfer has been refunded successfully.');
						}
					}else{
						$wallet->setAsFraud();
						$db_transaction->commit();

						$this->flash('error',
							'Player wallet has been detected as fraud.');
					}
				}
			}catch (Exception $exception){
				$db_transaction->rollBack();
			}catch (Throwable $exception){
				$db_transaction->rollBack();
			}

			if (!empty($exception)){
				$this->flash('error',
					'Cannot refunded the transfer right now. Please try again.');
			}
		}

		if (!$this->request->isAjax){
			return $this->goBack(['index']);
		}

		return $this->renderAjax('operator', [
			'model' => $model
		]);
	}
}
