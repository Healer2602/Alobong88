<?php

namespace frontend\base;

use Yii;
use yii\base\BaseObject;

/**
 * Currency rate convert
 */
class Rate extends BaseObject{

	/**
	 * @param $currency
	 *
	 * @return int
	 */
	public static function rate($currency = NULL){
		if (empty($currency)){
			$currency = Yii::$app->formatter->currencyCode;
		}

		if ($currency == 'VND'){
			return 1000;
		}

		return 1;
	}
}