<?php

namespace modules\customer\models;

use common\models\SettingForm;
use Yii;
use yii\base\InvalidArgumentException;
use yii\helpers\Json;

/**
 * Class Setting
 *
 * @package modules\customer\models
 *
 * @property-read array $pages
 * @property-read array $listCurrency
 */
class Setting extends SettingForm{

	public $currencies;
	public $terms_condition;
	public $privacy_policy;
	public $cookie_policy;
	public $lang;

	/**
	 * @return array
	 */
	public function rules(){
		return [
			[['terms_condition', 'privacy_policy', 'cookie_policy'], 'safe'],
			['lang', 'string'],
			['currencies', 'safe'],
		];
	}

	/**
	 * @return array
	 */
	public function attributeLabels(){
		return [
			'terms_condition' => Yii::t('customer', 'Terms and Conditions'),
			'privacy_policy'  => Yii::t('customer', 'Privacy Policy'),
			'cookie_policy'   => Yii::t('customer', 'Cookie Policy'),
			'currencies'      => Yii::t('customer', 'Currency'),
		];
	}

	/**
	 * @return array
	 */
	public function getPages(){
		return [];
	}

	/**
	 * @return false|int
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\db\Exception
	 */
	public function save(){
		if ($this->validate()){
			$this->currencies = strtoupper(Json::encode($this->currencies));
		}

		return parent::save();
	}

	/**
	 * @return array|mixed|void
	 */
	public function getListCurrency(){
		if (is_array($this->currencies)){
			return array_combine($this->currencies, $this->currencies);
		}

		try{
			$currencies = Json::decode($this->currencies);
			if (!empty($currencies)){
				return array_combine($currencies, $currencies);
			}
		}catch (InvalidArgumentException $exception){
			return [];
		}
	}

	/**
	 * @throws \yii\base\Exception
	 */
	public static function generateCode($length = 6){
		$random_string = Yii::$app->security->generateRandomString();
		$pattern       = '/([A-Za-z0-9]){' . $length . '}/';
		preg_match($pattern, $random_string, $matches);

		return strtoupper($matches[0] ?? '');
	}


}