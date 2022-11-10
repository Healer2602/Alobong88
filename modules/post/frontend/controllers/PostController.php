<?php

namespace modules\post\frontend\controllers;

use frontend\base\Controller;
use modules\post\frontend\models\Post;
use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * Class NewsController
 *
 * @package frontend\controllers
 */
class PostController extends Controller{

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
				'label' => Yii::t('post', 'Promotion'),
				'url'   => ["post/list", 'type' => $model->type]
			]
		];

		if (!empty($model->category)){
			$this->view->params['breadcrumbs'][] = [
				'label' => $model->category->name,
				'url'   => $model->category->url
			];
		}

		return $this->render('index', [
			'model' => $model
		]);
	}

	/**
	 * @param $type
	 *
	 * @return string
	 */
	public function actionList($type){
		if (empty($this->view->title)){
			$this->view->title = Yii::t('post', 'Promotion');
		}

		$this->view->params['breadcrumbs'] = [
			[
				'label' => $this->view->title,
				'url'   => ["post/list", 'type' => $type]
			]
		];

		$query = Post::find()->andWhere(['type' => $type])
		             ->orderBy(['created_at' => SORT_DESC])
		             ->with('category')
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

	/**
	 * @param $slug
	 *
	 * @return \modules\post\models\Post|null
	 * @throws \yii\web\NotFoundHttpException
	 */
	protected function findModel($slug){
		$model = Post::find()
		             ->andWhere(['slug' => $slug])
		             ->andWhere(['type' => Post::$types])
		             ->limit(1)->one();

		if (!empty($model)){
			return $model;
		}

		throw new NotFoundHttpException(Yii::t('post', 'Article does not exists.'));
	}
}