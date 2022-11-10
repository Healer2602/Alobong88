<?php

namespace common\models;

use common\models\Setting as BaseSetting;
use Yii;
use yii\base\Model;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;

/**
 * Class Setting
 *
 * @package common\models\settings
 */
class SettingForm extends Model{

	const SYSTEM_SETTING_CACHE = 'system-setting';

	private $_setting;

	/**
	 * @inheritDoc
	 */
	public function getValues(){
		if ($this->_setting === NULL){
			$settings = Yii::$app->cache->getOrSet(['system-setting-type', 'type' => static::class],
				function (){
					return BaseSetting::find()->asArray()->all();
				}, 0, new TagDependency([
					'tags' => self::SYSTEM_SETTING_CACHE
				]));

			$this->_setting = ArrayHelper::map($settings, 'key', 'value');
		}

		foreach ($this->attributes as $key => $attribute){
			$setting_key = strtoupper($key);
			$this->$key  = $this->_setting[$setting_key] ?? NULL;
		}
	}

	/**
	 * @return int
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\db\Exception
	 */
	public function save(){
		if ($this->validate()){
			$attributes = $this->attributes;
			$data       = [];

			foreach ($attributes as $attribute => $value){
				if (is_array($value) || is_object($value)){
					continue;
				}

				$data[] = new BaseSetting([
					'key'   => strtoupper($attribute),
					'value' => $value
				]);
			}

			TagDependency::invalidate(Yii::$app->cache, self::SYSTEM_SETTING_CACHE);

			$fields = BaseSetting::getTableSchema()->columnNames;
			BaseSetting::deleteAll(['key' => ArrayHelper::getColumn($data, 'key')]);

			if (BaseSetting::validateMultiple($data, $fields)){
				return Yii::$app->db->createCommand()
				                    ->batchInsert(BaseSetting::tableName(), $fields, $data)
				                    ->execute();
			}
		}

		return FALSE;
	}

}