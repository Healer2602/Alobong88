<?php

namespace modules\wallet\frontend\controllers;

use frontend\base\Controller;
use modules\customer\frontend\models\CustomerIdentity;
use modules\customer\models\CustomerBank;
use modules\wallet\frontend\models\Deposit;
use modules\wallet\frontend\models\Withdraw;
use modules\wallet\gateways\GatewayAbstract;
use modules\wallet\models\TopupGateway;
use modules\wallet\models\Transaction;
use modules\wallet\models\WithdrawGateway;
use Yii;
use yii\base\InvalidArgumentException;
use yii\bootstrap5\ActiveForm;
use yii\filters\AjaxFilter;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class DefaultController
 *
 * @package frontend\controllers
 */
class DefaultController extends Controller{

	/**
	 * {@inheritdoc}
	 */
	public function behaviors(){
		$behaviors = [
			'ajax'   => [
				'class' => AjaxFilter::class,
				'only' => ['validate', 'deposit-form', 'withdraw-form', 'add-bank']
			],
			'access' => [
				'rules' => [
					[
						'actions' => ['deposit', 'topup-return', 'withdraw', 'withdraw-return', 'history', 'get-exchange', 'validate', 'send-money', 'find-customer', 'deposit-form', 'withdraw-form', 'add-bank'],
						'roles'   => ['@'],
						'allow'   => TRUE,
					],
					[
						'actions' => ['topup-callback', 'withdraw-callback', 'account'],
						'allow'   => TRUE,
					],
				],
			],
		];

		return ArrayHelper::merge(parent::behaviors(), $behaviors);
	}

	/**
	 * @param \yii\base\Action $action
	 *
	 * @return bool
	 * @throws \yii\web\BadRequestHttpException
	 */
	public function beforeAction($action){
		if (ArrayHelper::isIn($action->id,
			['topup-return', 'topup-callback', 'withdraw-return', 'withdraw-callback'])){
			$this->enableCsrfValidation = FALSE;
		}

		return parent::beforeAction($action);
	}

	/**
	 * @return string|Response
	 */
	public function actionDeposit($opt = NULL){
		$model = new Deposit([
			'opt' => $opt
		]);

		if (($data = $this->request->post()) && $model->load($data)){
			$model->data = $data;
			$refresh     = FALSE;

			if ($model->send()){
				$this->flash('success', Yii::t('wallet',
					'Your deposit request has been processed successfully'));
				$refresh = TRUE;
			}elseif (empty($model->errors)){
				$this->flash('error', 'You cannot deposit right now. Please try again later.');
				$refresh = TRUE;
			}

			if ($refresh){
				return $this->refresh();
			}
		}

		if (empty($model->channels)){
			return $this->redirect(['deposit', 'opt' => $model->firstOption]);
		}

		return $this->render('deposit', [
			'model' => $model
		]);
	}

	/**
	 * @param $id
	 * @param string $type
	 *
	 * @return \yii\web\Response
	 */
	public function actionTopupReturn($id, $type = 'cancel'){
		$system_id = Yii::$app->session->get(Deposit::SESSION_TOPUP);
		if (!empty($system_id) && $id == $system_id){
			$transaction = Transaction::findOne($id);
		}

		if ($type == 'return'){
			if (!empty($transaction) && ($gateway = $transaction->gateway)){
				return $gateway->gateway->returnOrder($transaction);
			}
		}elseif ($type == 'cancel'){
			if (!empty($transaction) && $transaction->status == Transaction::STATUS_PENDING && $transaction->wallet->customer_id == Yii::$app->user->id){
				$transaction->description = Yii::t('wallet',
					'User has canceled the deposit request.');
				$transaction->status      = Transaction::STATUS_CANCELED;
				$transaction->save();
			}

			$this->flash('warning',
				Yii::t('wallet', 'Your deposit request has been canceled successfully'));
		}else{
			if (!empty($transaction) && $transaction->wallet->customer_id == Yii::$app->user->id){
				if ($transaction->status == Transaction::STATUS_SUCCESS){
					$this->flash('success', Yii::t('wallet',
						'Your deposit request has been processed successfully'));

					$message = TRUE;
				}
			}

			if (empty($message)){
				$this->flash('info', Yii::t('wallet',
					'Your deposit request is now being processed. Please be patient'));
			}
		}

		Yii::$app->session->remove(Deposit::SESSION_TOPUP);

		return $this->redirect(['deposit']);
	}

	/**
	 * @return bool
	 */
	public function actionTopupCallback($type){
		$gateway = TopupGateway::findOne(['key' => $type]);
		if (!empty($gateway)){
			return $gateway->gateway->IPN();
		}

		throw new InvalidArgumentException("Request is invalid.");
	}

	/**
	 * @param null $opt
	 *
	 * @return string|\yii\web\Response
	 */
	public function actionWithdraw($opt = NULL){
		if ($rank = CustomerIdentity::profile()->rank){
			$rank = $rank->toArray();
		}

		$model = new Withdraw([
			'opt'  => $opt,
			'rank' => $rank
		]);

		if (($data = $this->request->post()) && $model->load($data)){
			$model->data = $data;
			$refresh     = FALSE;
			if ($model->send()){
				$this->flash('success', Yii::t('wallet',
					'Your withdraw request has been processed successfully'));

				$refresh = TRUE;
			}elseif (empty($model->errors)){
				$this->flash('error', 'You cannot withdraw right now. Please try again later.');
				$refresh = TRUE;
			}

			if ($refresh){
				return $this->refresh();
			}
		}

		if (empty($model->channels)){
			return $this->redirect(['withdraw', 'opt' => $model->firstOption]);
		}

		return $this->render('withdraw', [
			'model' => $model
		]);
	}

	/**
	 * @param $id
	 * @param string $type
	 *
	 * @return \yii\web\Response
	 */
	public function actionWithdrawReturn($id, $type = 'cancel'){
		$system_id = Yii::$app->session->get(Withdraw::SESSION_WITHDRAW);
		if (!empty($system_id) && $id == $system_id){
			$transaction = Transaction::findOne($id);
		}

		if ($type == 'cancel'){
			if (!empty($transaction) && $transaction->wallet->customer_id == Yii::$app->user->id){
				if ($transaction->status == Transaction::STATUS_PROCESSING){
					$transaction->description = "\n" . Yii::t('wallet',
							'User has canceled the withdraw request.');
					$transaction->status      = Transaction::STATUS_CANCELED;
					$transaction->save();
					$this->flash('warning',
						Yii::t('wallet', 'Your withdraw request has been canceled successfully'));
				}else{
					$this->flash('error',
						Yii::t('wallet', 'Your withdraw request has been failed'));
				}

				if ($transaction->status == Transaction::STATUS_FAILED || $transaction->status == Transaction::STATUS_CANCELED){
					$wallet = $transaction->wallet;
					if (!empty($wallet) && $wallet->verify()){
						$wallet->balance += abs($transaction->amount);
						if ($wallet->save(FALSE)){
							$transaction = new Transaction([
								'amount'       => abs($transaction->amount),
								'status'       => Transaction::STATUS_SUCCESS,
								'wallet_id'    => $wallet->id,
								'type'         => Transaction::TYPE_RETURN,
								'description'  => Yii::t('wallet', "Return from withdrawal: {0}",
									[$transaction->transaction_id]),
								'reference_id' => $transaction->transaction_id
							]);

							$transaction->save();
						}
					}
				}
			}
		}else{
			if (!empty($transaction) && $transaction->wallet->customer_id == Yii::$app->user->id){
				if ($transaction->status == Transaction::STATUS_SUCCESS){
					$this->flash('success', Yii::t('wallet',
						'Your withdraw request has been processed successfully'));

					$message = TRUE;
				}
			}

			if (empty($message)){
				$this->flash('info', Yii::t('wallet',
					'Your withdraw request is now being processed. Please be patient'));
			}
		}

		Yii::$app->session->remove(Withdraw::SESSION_WITHDRAW);

		return $this->redirect(['withdraw']);
	}

	/**
	 * @return bool
	 */
	public function actionWithdrawCallback($type){
		$gateway = WithdrawGateway::findOne(['key' => $type]);
		if (!empty($gateway)){
			return $gateway->gateway->IPN(GatewayAbstract::TYPE_WITHDRAW);
		}

		throw new InvalidArgumentException("Request is invalid.");
	}

	/**
	 * @param $opt
	 *
	 * @return string
	 */
	public function actionDepositForm($opt){
		$gateway_id = $this->request->post('id');

		$model = new Deposit([
			'opt'     => $opt,
			'gateway' => $gateway_id
		]);

		$form = ActiveForm::begin([
			'layout'      => ActiveForm::LAYOUT_HORIZONTAL,
			'fieldConfig' => [
				'horizontalCssClasses' => [
					'label'   => 'col-lg-3 mb-2 pt-1',
					'wrapper' => 'col-lg-9 mb-2',
				],
				'options'              => ['class' => 'row']
			],
		]);

		return $this->renderAjax($model->gatewayModel->formPath, [
			'model'   => $model->model,
			'gateway' => $model,
			'form'    => $form
		]);
	}

	/**
	 * @param $opt
	 *
	 * @return string
	 */
	public function actionWithdrawForm($opt){
		$gateway_id = $this->request->post('id');

		$model = new Withdraw([
			'opt'     => $opt,
			'gateway' => $gateway_id,
		]);

		$form = ActiveForm::begin([
			'layout'      => ActiveForm::LAYOUT_HORIZONTAL,
			'fieldConfig' => [
				'horizontalCssClasses' => [
					'label'   => 'col-lg-3 mb-2 pt-1',
					'wrapper' => 'col-lg-9 mb-2',
				],
				'options'              => ['class' => 'row']
			],
		]);

		return $this->renderAjax($model->gatewayModel->formPath, [
			'model'   => $model->model,
			'gateway' => $model,
			'form'    => $form
		]);
	}

	/**
	 * @return array|string
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function actionAddBank(){
		$customer_bank_model        = new CustomerBank([
			'customer_id'  => Yii::$app->user->getId(),
			'account_name' => CustomerIdentity::profile()->name,
		]);
		Yii::$app->response->format = Response::FORMAT_JSON;

		if (Yii::$app->request->isAjax && $customer_bank_model->load(Yii::$app->request->post())){
			$validate = ActiveForm::validate($customer_bank_model);
			if (!empty($validate)){
				return ['validate' => $validate];
			}elseif ($customer_bank_model->save()){
				return ['success' => TRUE];
			}
		}

		if (!$this->request->isAjax){
			throw new NotFoundHttpException(Yii::t('common', 'Page does not exists.'));
		}

		return $this->renderAjax('_form-add-bank', [
			'model' => $customer_bank_model
		]);
	}
}