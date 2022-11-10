<?php

namespace modules\post\frontend\models;

use common\base\ActiveQuery;
use modules\post\models\Category as BaseCategory;

/**
 * Class PublicCategory
 *
 * @package modules\post\frontend\models
 *
 * @inheritDoc
 */
class Category extends BaseCategory{

	public $total = 0;

	/**
	 * @return \common\base\ActiveQuery
	 */
	public static function find(){
		$query = new ActiveQuery(get_called_class());

		return $query->default();
	}

	/**
	 * @return \yii\db\ActiveQuery|ActiveQuery
	 */
	public function getPosts(){
		return $this->hasMany(Post::class, ['category_id' => 'id']);
	}
}