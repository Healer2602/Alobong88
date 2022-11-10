<?php

namespace modules\post\widgets;

use modules\post\frontend\models\Post;
use yii\base\Widget;

/**
 * Class SidebarBlog
 *
 * @package frontend\widgets
 */
class Sidebar extends Widget{

	/**
	 * @throws \Throwable
	 */
	public function run(){
		$data = Post::find()
		            ->orderBy(['created_at' => SORT_DESC])
		            ->andWhere(['type' => Post::$types])
		            ->andWhere(['LIKE', 'tags', 'featured'])
		            ->limit(10)
		            ->translate()
		            ->all();

		return $this->render('sidebar', [
			'data' => $data,
		]);
	}

}