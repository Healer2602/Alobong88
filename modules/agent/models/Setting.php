<?php

namespace modules\agent\models;

use common\models\SettingForm;
use Yii;
use yii\helpers\Json;

/**
 * Class Setting
 *
 * @package modules\agent\models
 *
 * @property-read array $pages
 * @property-read array $listCurrency
 */
class Setting extends SettingForm{

	public $range_1;
	public $range_2;
	public $range_3;

	/**
	 * @return array
	 */
	public function rules(){
		return [
			[['range_1', 'range_2', 'range_3'], 'safe'],
		];
	}

	/**
	 * @return array
	 */
	public function attributeLabels(){
		return [
			'range_1' => Yii::t('agent', 'Range 1'),
			'range_2' => Yii::t('agent', 'Range 2'),
			'range_3' => Yii::t('agent', 'Range 3'),
		];
	}

	/**
	 * @return false|int
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\db\Exception
	 */
	public function save(){
		if ($this->validate()){
			$this->range_1 = Json::encode($this->range_1);
			$this->range_2 = Json::encode($this->range_2);
			$this->range_3 = Json::encode($this->range_3);
		}

		return parent::save();
	}

	public function getValues(){
		parent::getValues();

		$this->range_1 = Json::decode($this->range_1);
		$this->range_2 = Json::decode($this->range_2);
		$this->range_3 = Json::decode($this->range_3);
	}
}