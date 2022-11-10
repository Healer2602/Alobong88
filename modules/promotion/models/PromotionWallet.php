<?php

namespace modules\promotion\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%promotion_wallet}}".
 *
 * @property int $promotion_id
 * @property string $product_code
 * @property Promotion $promotion
 * @property ProductWallet $productWallet
 */
class PromotionWallet extends ActiveRecord{

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%promotion_wallet}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['promotion_id', 'product_code'], 'required'],
			[['promotion_id'], 'integer'],
			[['product_code'], 'string', 'max' => 255],
			[['promotion_id', 'product_code'], 'unique', 'targetAttribute' => ['promotion_id', 'product_code']],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'promotion_id' => Yii::t('promotion', 'Promotion ID'),
			'product_code' => Yii::t('promotion', 'Product Code'),
		];
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getPromotion(){
		return $this->hasOne(Promotion::class, ['id' => 'promotion_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getProductWallet(){
		return $this->hasOne(ProductWallet::class, ['code' => 'product_code']);
	}

	/**
	 * @param $rows
	 *
	 * @return int
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\db\Exception
	 */
	public static function upsert($rows){
		if (self::validateMultiple($rows)){
			return Yii::$app->db->createCommand()
			                    ->batchInsert(self::tableName(),
				                    self::getTableSchema()->columnNames, $rows)
			                    ->execute();
		}

		return FALSE;
	}
}