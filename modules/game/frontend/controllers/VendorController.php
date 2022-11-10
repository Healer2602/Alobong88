<?php

namespace modules\game\frontend\controllers;

use common\base\Status;
use frontend\base\Controller;
use modules\game\models\Vendor;
use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * Class VendorController
 *
 * @package frontend\controllers
 */
class VendorController extends Controller{

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
	 * @return string
	 */
	public function actionIndex(){
		$data = Vendor::find()
		              ->andWhere(['NOT', ['icon' => ['', NULL]]])
		              ->orderBy(['name' => SORT_ASC])
		              ->limit(- 1)
		              ->all();

		$this->view->title = Yii::t('game', 'All Partners');

		return $this->render('index', [
			'data' => $data
		]);
	}

	/**
	 * @param $slug
	 *
	 * @return string
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function actionView($slug){
		$model = $this->findModel($slug);
		$query = $model->getGames()->default()
		               ->with(['detailZh', 'detailVi'])
		               ->orderBy(['ordering' => SORT_ASC])
		               ->andFilterCompare('name', $this->request->get('s'), 'LIKE');

		$count_query = clone $query;
		$pagination  = new Pagination([
			'totalCount'      => $count_query->count(),
			'defaultPageSize' => 30,
		]);

		$data = $query->offset($pagination->offset)
		              ->limit($pagination->limit)
		              ->all();

		$this->view->title = Yii::t('game', 'Games from {0}', $model->name);

		return $this->render('view', [
			'model'      => $model,
			'data'       => $data,
			'pagination' => $pagination
		]);
	}

	/**
	 * @param string $slug
	 *
	 * @return \modules\game\models\Vendor
	 * @throws \yii\web\NotFoundHttpException
	 */
	protected function findModel($slug)
	: Vendor{
		$model = Vendor::findOne(['slug' => $slug, 'status' => Status::STATUS_ACTIVE]);
		if (empty($model)){
			throw new NotFoundHttpException(Yii::t('common', 'Page not found'));
		}

		return $model;
	}
}