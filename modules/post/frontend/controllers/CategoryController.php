<?php

namespace modules\post\frontend\controllers;

use frontend\base\Controller;
use modules\post\frontend\models\Category;
use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

/**
 * Class CategoryController
 *
 * @package frontend\controllers
 */
class CategoryController extends Controller{

	/**
	 * {@inheritdoc}
	 */
	public function behaviors(){
		$behaviors = [
			'access' => [
				'rules' => [
					[
						'actions' => ['index'],
						'allow'   => TRUE,
					]
				],
			]
		];

		return ArrayHelper::merge(parent::behaviors(), $behaviors);
	}

	/**
	 * @param string $slug
	 *
	 * @return \yii\web\Response|string
	 * @throws \Throwable
	 */
	public function actionIndex($slug){
		$model = $this->findModel($slug);
		if (empty($model)){
			return $this->goHome();
		}

		if (empty($this->view->title)){
			$this->view->title = $model->name;
		}

		$this->view->params['breadcrumbs'] = [
			[
				'label' => Yii::t('post', 'Promotion'),
				'url'   => ["post/list", 'type' => $model->type]
			],
			[
				'label' => $model->name,
				'url'   => $model->url
			]
		];

		$query = $model->getPosts()
		               ->translate()
		               ->orderBy(['created_at' => SORT_DESC])
		               ->with('category');

		$count      = $query->count();
		$pagination = new Pagination([
			'totalCount' => $count
		]);

		$posts = $query->offset($pagination->offset)
		               ->limit($pagination->limit)
		               ->all();

		return $this->render("/post/post", [
			'posts'      => $posts,
			'model'      => $model,
			'pagination' => $pagination,
			'type'       => $model->type
		]);
	}

	/**
	 * @param $slug
	 *
	 * @return Category|null
	 */
	protected function findModel($slug){
		return Category::find()
		               ->translate()
		               ->andWhere(['slug' => $slug])
		               ->limit(1)
		               ->one();
	}
}