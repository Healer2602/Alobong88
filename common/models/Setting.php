<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%setting}}".
 *
 * @property string $key
 * @property string $value
 */
class Setting extends ActiveRecord{

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%setting}}';
	}

	/**
	 * @return array|string[]
	 */
	public static function primaryKey(){
		return ['key'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['value'], 'string'],
			[['key'], 'string', 'max' => 255],
			[['key'], 'unique'],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'    => Yii::t('common', 'ID'),
			'key'   => Yii::t('common', 'Key'),
			'value' => Yii::t('common', 'Value'),
		];
	}
}
