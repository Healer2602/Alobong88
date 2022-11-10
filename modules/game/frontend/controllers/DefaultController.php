<?php

namespace modules\game\frontend\controllers;

use common\base\Status;
use frontend\base\Controller;
use modules\game\models\Game;
use modules\game\models\GamePlay;
use modules\matrix\base\Game as MatrixGame;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;

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
			'access' => [
				'rules' => [
					[
						'allow' => TRUE,
					],
				],
			],
		];

		return ArrayHelper::merge(parent::behaviors(), $behaviors);
	}


	/**
	 * @param int $id
	 *
	 * @return \yii\web\Response
	 * @throws \Exception
	 */
	public function actionIndex($id){
		$model = $this->findModel($id);

		if (Yii::$app->user->isGuest){
			Yii::$app->user->setReturnUrl(Yii::$app->request->getAbsoluteUrl());
			$this->flash('info', "You need to sign in before continuing.");

			return $this->redirect(['/customer/default/sign-in']);
		}

		if ($this->request->get('lb')){
			$type_url = Url::to(['/'], TRUE);
		}else{
			$type_url = Url::to($model->type->url, TRUE);
		}

		$game_url = MatrixGame::play($model, $type_url);

		if (!empty($game_url)){
			GamePlay::store([
				'game_id'      => $model->id,
				'player_id'    => Yii::$app->user->id,
				'product_code' => $model->productWallet->code
			]);

			return $this->redirect($game_url);
		}

		return $this->redirect($type_url);
	}

	/**
	 * @param int $id
	 *
	 * @return \yii\web\Response
	 * @throws \Exception
	 */
	public function actionTry($id){
		$model    = $this->findModel($id);
		$type_url = Url::to($model->type->url, TRUE);
		if ($model->free_to_play){
			$game_url = MatrixGame::play($model, $type_url, TRUE);

			if (!empty($game_url)){
				return $this->redirect($game_url);
			}
		}

		return $this->redirect($type_url);
	}

	/**
	 * @param $id
	 *
	 * @return \modules\game\models\Game
	 * @throws \yii\web\NotFoundHttpException
	 */
	protected function findModel($id)
	: Game{
		$model = Game::findOne(['id' => $id, 'status' => Status::STATUS_ACTIVE]);
		if (empty($model)){
			throw new NotFoundHttpException(Yii::t('common', 'Page not found'));
		}

		return $model;
	}
}