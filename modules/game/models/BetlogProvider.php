<?php

namespace modules\game\models;

use common\models\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%betlog_provider}}".
 *
 * @property int $id
 * @property string $code
 * @property int $vendor_id
 * @property string $product_wallet
 * @property int $status
 * @property int $created_at
 * @property int $created_by
 * @property int $updated_at
 * @property int $updated_by
 *
 * @property Vendor $vendor
 * @property array $vendors
 */
class BetlogProvider extends BaseActiveRecord{

	public static $alias = 'betlog_provider';

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%betlog_provider}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['vendor_id', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
			[['code', 'product_wallet'], 'string', 'max' => 255],
			[['code', 'product_wallet'], 'required'],
			[['code'], 'unique'],
			[['vendor_id'], 'exist', 'skipOnError' => TRUE, 'targetClass' => Vendor::class, 'targetAttribute' => ['vendor_id' => 'id']],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'             => Yii::t('game', 'ID'),
			'code'           => Yii::t('game', 'Code'),
			'vendor_id'      => Yii::t('game', 'Vendor'),
			'product_wallet' => Yii::t('game', 'Product Wallet'),
			'status'         => Yii::t('common', 'Status'),
			'created_at'     => Yii::t('common', 'Created At'),
			'created_by'     => Yii::t('common', 'Created By'),
			'updated_at'     => Yii::t('common', 'Updated At'),
			'updated_by'     => Yii::t('common', 'Updated By'),
		];
	}

	/**
	 * @return array
	 */
	public function behaviors(){
		$behaviors                    = parent::behaviors();
		$behaviors['audit']['module'] = Yii::t('game', 'Betlog Provider');

		return $behaviors;
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getVendor(){
		return $this->hasOne(Vendor::class, ['id' => 'vendor_id']);
	}

	/**
	 * @return array
	 * @throws \Throwable
	 */
	public function getVendors(){
		return Vendor::findList();
	}
}