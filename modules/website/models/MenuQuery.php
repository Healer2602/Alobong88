<?php

namespace modules\website\models;

use common\base\ActiveQuery;
use creocoder\nestedsets\NestedSetsQueryBehavior;

/**
 * Class MenuQuery
 *
 * @package modules\website\models
 */
class MenuQuery extends ActiveQuery{

	/**
	 * @return array
	 */
	public function behaviors(){
		return [
			NestedSetsQueryBehavior::class,
		];
	}
}