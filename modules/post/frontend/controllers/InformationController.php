<?php

namespace modules\post\frontend\controllers;

use frontend\base\Controller;
use modules\post\frontend\models\Information;
use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * Class InformationController
 *
 * @package frontend\controllers
 */
class InformationController extends Controller{

	/**
	 * {@inheritdoc}
	 */
	public function behaviors(){
		$behaviors = [
			'access' => [
				'rules' => [
					[
						'actions' => ['index', 'list'],
						'allow'   => TRUE,
					]
				],
			]
		];

		return ArrayHelper::merge(parent::behaviors(), $behaviors);
	}

	/**
	 * @param $slug
	 *
	 * @return string
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function actionIndex($slug){
		$model = $this->findModel($slug);

		if (empty($this->view->title)){
			$this->view->title = $model->name;
		}

		$this->view->params['breadcrumbs'] = [
			[
				'label' => Yii::t('post', 'Information'),
				'url'   => ["information/list", 'type' => $model->type]
			]
		];

		return $this->render('index', [
			'model' => $model
		]);
	}

	/**
	 * @param $slug
	 *
	 * @return \modules\post\models\Post|null
	 * @throws \yii\web\NotFoundHttpException
	 */
	protected function findModel($slug){
		$model = Information::find()
		                    ->andWhere(['slug' => $slug])
		                    ->andWhere(['type' => Information::$types])
		                    ->translate()
		                    ->limit(1)->one();

		if (!empty($model)){
			return $model;
		}

		throw new NotFoundHttpException(Yii::t('post', 'Information does not exists.'));
	}

	/**
	 * @param $type
	 *
	 * @return string
	 * @throws \yii\base\InvalidConfigException
	 */
	public function actionList($type){
		if (empty($this->view->title)){
			$this->view->title = Yii::t('post', 'Information');
		}

		$this->view->params['breadcrumbs'] = [
			[
				'label' => $this->view->title,
				'url'   => ["information/list", 'type' => $type]
			]
		];

		$query = Information::find()->andWhere(['type' => $type])
		                    ->orderBy(['created_at' => SORT_DESC])
		                    ->translate();

		$count      = $query->count();
		$pagination = new Pagination([
			'totalCount' => $count
		]);

		$posts = $query->offset($pagination->offset)
		               ->limit($pagination->limit)
		               ->all();

		return $this->render($type, [
			'posts'      => $posts,
			'pagination' => $pagination,
			'type'       => $type,
		]);
	}
}