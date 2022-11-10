<?php

namespace modules\customer\models;

use modules\customer\frontend\models\CustomerIdentity;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%customer_auth}}".
 *
 * @property int $id
 * @property int $customer_id
 * @property string $source
 * @property string $source_id
 *
 * @property CustomerIdentity $customer
 */
class CustomerAuth extends ActiveRecord{

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%customer_auth}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['customer_id', 'source', 'source_id'], 'required'],
			[['customer_id'], 'integer'],
			[['source', 'source_id'], 'string', 'max' => 255],
			[['customer_id'], 'exist', 'skipOnError' => TRUE, 'targetClass' => Customer::class, 'targetAttribute' => ['customer_id' => 'id']],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'          => Yii::t('customer', 'ID'),
			'customer_id' => Yii::t('customer', 'Player'),
			'source'      => Yii::t('customer', 'Source'),
			'source_id'   => Yii::t('customer', 'Source'),
		];
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getCustomer(){
		return $this->hasOne(CustomerIdentity::class, ['id' => 'customer_id']);
	}
}
