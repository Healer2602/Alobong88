<?php

namespace modules\post\frontend\models;

use common\base\ActiveQuery;
use modules\post\models\Post as BasePost;

/**
 * Class PublicPost
 *
 * @package modules\post\frontend\models
 */
class Post extends BasePost{

	public static $types = ['post'];

	/**
	 * @return \common\base\ActiveQuery
	 */
	public static function find(){
		$query = new ActiveQuery(get_called_class());

		return $query->distinct()->default();
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getCategory(){
		return $this->hasOne(Category::class, ['id' => 'category_id']);
	}
}