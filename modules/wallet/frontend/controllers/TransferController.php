<?php

namespace modules\wallet\frontend\controllers;

use frontend\base\Controller;
use modules\customer\frontend\models\CustomerIdentity;
use modules\game\models\ProductWallet;
use modules\wallet\console\jobs\RestoreJob;
use modules\wallet\frontend\models\Transfer;
use modules\wallet\models\Wallet;
use modules\wallet\models\WalletSub;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Response;

/**
 * Transfer wallet
 */
class TransferController extends Controller{

	/**
	 * {@inheritdoc}
	 */
	public function behaviors(){
		$behaviors = [
			'access' => [
				'rules' => [
					[
						'roles' => ['@'],
						'allow' => TRUE,
					],
				],
			],
			'verbs'  => [
				'actions' => [
					'allin' => ['POST'],
					'auto'  => ['POST'],
				]
			]
		];

		return ArrayHelper::merge(parent::behaviors(), $behaviors);
	}

	/**
	 * @return string|\yii\web\Response
	 * @throws \yii\base\Exception
	 */
	public function actionIndex(){
		$model = new Transfer([
			'amount' => floor(Wallet::my()->balance)
		]);

		if ($model->load($this->request->post())){
			if ($model->submit()){
				$this->flash('success',
					Yii::t('wallet', 'Wallet amount has been transferred successfully.'));
			}elseif (empty($model->errors)){
				$this->flash('error',
					Yii::t('wallet', 'Cannot transfer right now. Please try again.'));
			}

			return $this->refresh();
		}

		return $this->render('index', [
			'model' => $model
		]);
	}

	/**
	 * @return \yii\web\Response
	 * @throws \yii\base\Exception
	 */
	public function actionAllin(){
		if ($target = $this->findWallet($this->request->post('id'))){
			if ($target instanceof WalletSub){
				$model = new Transfer();

				if ($model->allin($target)){
					$this->flash('success',
						Yii::t('wallet', 'Wallet amount has been transferred successfully.'));
				}else{
					$this->flash('error',
						Yii::t('wallet', 'Cannot transfer right now. Please try again.'));
				}
			}else{
				$this->flash('error',
					Yii::t('wallet',
						'Channel has been blocked due to you joined the promotion.'));
			}
		}

		return $this->redirect(['index']);
	}

	/**
	 * @return array
	 */
	public function actionAuto(){
		Yii::$app->response->format = Response::FORMAT_JSON;

		$wallet = CustomerIdentity::profile()->wallet ?? NULL;
		if (!empty($wallet)){
			Wallet::updateAll(['auto_transfer' => abs(1 - $wallet->auto_transfer)],
				['id' => $wallet->id]);
		}

		return [];
	}

	/**
	 * @return \yii\web\Response
	 */
	public function actionRestore(){
		/**@var \common\base\Queue $queue */
		$queue = Yii::$app->queue;

		$added = $queue->push(new RestoreJob([
			'wallet_id' => CustomerIdentity::profile()->wallet->id ?? NULL
		]));

		if ($added){
			$this->flash('success',
				Yii::t('wallet', 'Your restore request is now being processed.'));
		}

		return $this->back();
	}

	/**
	 * @param $id
	 *
	 * @return array|\modules\game\models\ProductWallet|\yii\db\ActiveRecord|null
	 */
	private function findModel($id){
		return ProductWallet::find()
		                    ->default()
		                    ->andWhere(['code' => $id])
		                    ->one();
	}

	/**
	 * @param $id
	 *
	 * @return array|\modules\wallet\models\WalletSub|\yii\db\ActiveRecord|null|integer
	 * @throws \yii\base\Exception
	 */
	private function findWallet($id = NULL){
		/**@var Wallet $main_wallet */
		$main_wallet = Wallet::my()->id ?? NULL;
		if (empty($id) || empty($main_wallet)){
			return NULL;
		}

		$model = WalletSub::find()
		                  ->andWhere(['product_code' => $id])
		                  ->andWhere(['wallet_id' => $main_wallet])
		                  ->one();

		if (!empty($model)){
			if ($model->status != WalletSub::STATUS_ACTIVE){
				return - 1;
			}

			return $model;
		}

		$model = new WalletSub([
			'product_code' => $id,
			'wallet_id'    => $main_wallet,
			'player_id'    => Yii::$app->user->id,
			'balance'      => 0,
			'verify_hash'  => Yii::$app->security->generatePasswordHash('0')
		]);

		if ($model->save()){
			return $model;
		}

		return NULL;
	}
}