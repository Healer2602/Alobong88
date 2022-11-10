<?php

namespace modules\post\widgets;

use modules\post\frontend\models\Category as Model;
use modules\post\frontend\models\Post;
use yii\base\Widget;

/**
 * Class Category
 *
 * @package frontend\widgets
 */
class Category extends Widget{

	/**
	 * @throws \Throwable
	 */
	public function run(){
		$data = Model::find()
		             ->alias('category')
		             ->select(['category.*', 'total' => 'COUNT(post.id)'])
		             ->joinWith('posts post', FALSE)
		             ->orderBy(['category.created_at' => SORT_DESC])
		             ->andWhere(['category.type' => Post::$types])
		             ->orderBy(['category.name' => SORT_ASC])
		             ->groupBy(['category.id'])
		             ->having(['>', 'COUNT(post.id)', 0])
		             ->limit(- 1)
		             ->translate()
		             ->all();

		return $this->render('category', [
			'data' => $data,
		]);
	}

}