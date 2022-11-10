<?php

namespace modules\game\frontend\controllers;

use common\base\Status;
use frontend\base\Controller;
use Mobile_Detect;
use modules\game\models\GamePlay;
use modules\game\models\GameType;
use modules\game\models\Vendor;
use modules\game\models\VendorContent;
use modules\matrix\base\Game as MatrixGame;
use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;


/**
 * Class DefaultController
 *
 * @package frontend\controllers
 */
class TypeController extends Controller{

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
	 * @param $slug
	 *
	 * @return string|\yii\web\Response
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\httpclient\Exception
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function actionIndex($slug){
		$model             = $this->findModel($slug);
		$this->view->title = Yii::t('game', $model->name);

		if ($model->layout === GameType::TYPE_SPORT){
			return $this->renderIframe($model);
		}

		$partner_id = NULL;
		if ($partner = $this->request->get('partner')){
			$partner_model = $this->findPartner($partner);
			$partner_id    = $partner_model->id;
		}

		if (empty($partner_id)){
			$vendors = $model->getVendorContents()
			                 ->andWhere(['vendor_content.status' => Status::STATUS_ACTIVE])
			                 ->all();

			if (!empty($vendors)){
				return $this->renderVendors($model, $vendors);
			}
		}

		return $this->renderGames($model, $partner_id);
	}

	/**
	 * @param \modules\game\models\GameType $model
	 *
	 * @return string|\yii\web\Response
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\httpclient\Exception
	 */
	protected function renderIframe(GameType $model){
		if (Yii::$app->user->isGuest){
			Yii::$app->user->setReturnUrl(Yii::$app->request->getAbsoluteUrl());
			$this->flash('info', "You need to sign in before continuing.");

			return $this->redirect(['/customer/default/sign-in']);
		}

		/**@var \modules\game\models\Game $game */
		$game = $model->getGames()->default()->one();
		if (!empty($game)){
			$game_url = MatrixGame::play($game, '');

			if (!empty($game_url)){
				GamePlay::store([
					'game_id'      => $game->id,
					'player_id'    => Yii::$app->user->id,
					'product_code' => $game->productWallet->code
				]);

				$mobile = new Mobile_Detect();
				if ($mobile->isiOS() || $mobile->isiPadOS()){
					return $this->redirect($game_url);
				}

				return $this->render($model->layout, [
					'model' => $model,
					'game'  => $game_url
				]);
			}
		}

		return $this->goHome();
	}


	/**
	 * @param \modules\game\models\GameType $model
	 * @param $partner_id
	 *
	 * @return string|\yii\web\Response
	 */
	protected function renderGames($model, $partner_id = NULL){
		$query = $model->getGames()->default()
		               ->with(['detailZh', 'detailVi'])
		               ->orderBy(['ordering' => SORT_ASC])
		               ->andFilterCompare('name', $this->request->get('s'), 'LIKE')
		               ->andFilterWhere(['vendor_id' => $partner_id]);

		$count_query = clone $query;
		$pagination  = new Pagination([
			'totalCount'      => $count_query->count(),
			'defaultPageSize' => 20,
		]);

		$data = $query->offset($pagination->offset)
		              ->limit($pagination->limit)
		              ->all();

		if (!$this->request->get('s') && $pagination->totalCount == 1 && !empty($data[0])){
			return $this->redirect(ArrayHelper::merge($data[0]->url, ['lb' => 1]));
		}

		return $this->render('default', [
			'model'      => $model,
			'data'       => $data,
			'pagination' => $pagination
		]);
	}

	/**
	 * @param \modules\game\models\GameType $model
	 * @param VendorContent[] $vendor_contents
	 *
	 * @return string
	 */
	protected function renderVendors(GameType $model, array $vendor_contents){
		return $this->render('vendors', [
			'model'   => $model,
			'vendors' => $vendor_contents
		]);
	}

	/**
	 * @param string $slug
	 *
	 * @return \modules\game\models\GameType
	 * @throws \yii\web\NotFoundHttpException
	 */
	protected function findModel($slug)
	: GameType{
		$model = GameType::findOne(['slug' => $slug, 'status' => Status::STATUS_ACTIVE]);
		if (empty($model)){
			throw new NotFoundHttpException(Yii::t('common', 'Page not found'));
		}

		return $model;
	}

	/**
	 * @param string $slug
	 *
	 * @return \modules\game\models\Vendor
	 * @throws \yii\web\NotFoundHttpException
	 */
	protected function findPartner($slug)
	: Vendor{
		$model = Vendor::findOne(['slug' => $slug, 'status' => Status::STATUS_ACTIVE]);
		if (empty($model)){
			throw new NotFoundHttpException(Yii::t('common', 'Page not found'));
		}

		return $model;
	}
}