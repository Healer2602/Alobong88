<?php

namespace common\base;

use Yii;
use yii\i18n\Formatter as BaseFormatter;

/**
 * Class Formatter
 *
 * @package common\base
 */
class Formatter extends BaseFormatter{

	/**
	 * @inheritDoc
	 */
	public function asCurrency($value, $currency = NULL, $options = [], $textOptions = []){
		if (!empty($value)){
			$value = floatval($value);
		}

		if (Yii::$app->formatter->currencyCode == 'VND'){
			$value = floor($value);
		}

		$currency = parent::asCurrency($value, $currency = NULL, $options = [],
			$textOptions = []);

		return str_replace(".00", "", $currency);
	}

	/**
	 * @inheritDoc
	 */
	public function asPercent($value, $decimals = NULL, $options = [], $textOptions = []){
		$percent = parent::asPercent($value, $decimals, $options, $textOptions);

		return str_replace(".0%", '%', $percent);
	}
}