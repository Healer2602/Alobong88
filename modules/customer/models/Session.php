<?php

namespace modules\customer\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%session}}".
 *
 * @property string $id
 * @property int $expire
 * @property resource $data
 * @property int $customer_id
 */
class Session extends ActiveRecord{

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%session}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['id'], 'required'],
			[['expire', 'customer_id'], 'integer'],
			[['data'], 'string'],
			[['id'], 'string', 'max' => 40],
			[['id'], 'unique'],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'          => Yii::t('customer', 'ID'),
			'expire'      => Yii::t('customer', 'Expire'),
			'data'        => Yii::t('customer', 'Data'),
			'customer_id' => Yii::t('customer', 'Player ID'),
		];
	}
}
