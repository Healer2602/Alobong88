<?php

namespace modules\notification\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%trigger}}".
 *
 * @property int $id
 * @property string $key
 * @property string $name
 * @property string|array $level
 * @property string|array $params
 *
 * @property array $templateParams
 * @property array $emailParams
 */
class Trigger extends ActiveRecord{

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%trigger}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['key', 'name'], 'required'],
			[['key', 'level'], 'string', 'max' => 255],
			[['name', 'params'], 'string', 'max' => 1000],
			[['key'], 'unique'],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'     => Yii::t(' backend', 'ID'),
			'key'    => Yii::t(' backend', 'Key'),
			'name'   => Yii::t(' backend', 'Name'),
			'level'  => Yii::t(' backend', 'Level'),
			'params' => Yii::t(' backend', 'Params'),
		];
	}

	/**
	 * @param bool $insert
	 *
	 * @return bool
	 */
	public function beforeSave($insert){
		if (is_array($this->level)){
			$this->level = Json::encode($this->level);
		}

		if (is_array($this->params)){
			$this->params = Json::encode($this->params);
		}

		return parent::beforeSave($insert);
	}

	/**
	 * @inheritDoc
	 */
	public function afterFind(){
		if (is_string($this->level)){
			$this->level = Json::decode($this->level);
		}

		if (is_string($this->params)){
			$this->params = Json::decode($this->params);
		}

		parent::afterFind();
	}

	/**
	 * @return array
	 */
	public function getTemplateParams(){
		return ArrayHelper::getColumn($this->params, function ($data){
			return '[' . $data . ']';
		});
	}

	/**
	 * @return array
	 */
	public function getEmailParams(){
		return ArrayHelper::map($this->params, function ($data){
			return $data;
		}, function ($data){
			return '[' . $data . ']';
		});
	}

	/**
	 * @return array
	 */
	public static function findList(){
		$triggers = Trigger::find()->asArray()->all();

		return ArrayHelper::map($triggers, 'key', 'name');
	}
}
