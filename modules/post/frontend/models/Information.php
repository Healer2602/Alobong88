<?php

namespace modules\post\frontend\models;

use common\base\ActiveQuery;
use modules\post\models\Post as BasePost;

/**
 * Class PublicPost
 *
 * @package modules\post\frontend\models
 *
 * @property array $url
 */
class Information extends BasePost{

	public static $types = ['information'];

	/**
	 * @return \common\base\ActiveQuery
	 */
	public static function find(){
		$query = new ActiveQuery(get_called_class());

		return $query->distinct()->default();
	}
}