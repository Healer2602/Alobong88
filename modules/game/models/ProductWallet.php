<?php

namespace modules\game\models;

use common\models\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%product_wallet}}".
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property int $type_id
 * @property int $vendor_id
 * @property int $status
 * @property float $rate
 * @property int $created_at
 * @property int $created_by
 * @property int $updated_at
 * @property int $updated_by
 *
 * @property GameType $type
 * @property Vendor $vendor
 * @property array $types
 * @property array $vendors
 */
class ProductWallet extends BaseActiveRecord{

	public static $alias = 'product_wallet';

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%product_wallet}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['name', 'code'], 'required'],
			[['type_id', 'vendor_id', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
			[['name', 'code'], 'string', 'max' => 255],
			[['type_id', 'vendor_id'], 'unique', 'targetAttribute' => ['type_id', 'vendor_id']],
			[['type_id'], 'exist', 'skipOnError' => TRUE, 'targetClass' => GameType::class, 'targetAttribute' => ['type_id' => 'id']],
			[['vendor_id'], 'exist', 'skipOnError' => TRUE, 'targetClass' => Vendor::class, 'targetAttribute' => ['vendor_id' => 'id']],
			['rate', 'default', 'value' => 1],
			['rate', 'number']
		];
	}

	/**
	 * @return array
	 */
	public function behaviors(){
		$behaviors                    = parent::behaviors();
		$behaviors['audit']['module'] = Yii::t('game', 'Product Wallet');

		return $behaviors;
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'         => Yii::t('game', 'ID'),
			'name'       => Yii::t('game', 'Name'),
			'code'       => Yii::t('game', 'Code'),
			'type_id'    => Yii::t('game', 'Game Type'),
			'vendor_id'  => Yii::t('game', 'Vendor'),
			'status'     => Yii::t('game', 'Status'),
			'created_at' => Yii::t('game', 'Created At'),
			'created_by' => Yii::t('game', 'Created By'),
			'updated_at' => Yii::t('game', 'Updated At'),
			'updated_by' => Yii::t('game', 'Updated By'),
			'rate'       => Yii::t('game', 'Rate'),
		];
	}

	/**
	 * @param $insert
	 * @param $changedAttributes
	 *
	 * @return void
	 */
	public function afterSave($insert, $changedAttributes){
		parent::afterSave($insert, $changedAttributes);

		self::updateAll(['rate' => $this->rate], ['code' => $this->code]);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getType(){
		return $this->hasOne(GameType::class, ['id' => 'type_id']);
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
	public function getTypes(){
		return GameType::findList();
	}

	/**
	 * @return array
	 * @throws \Throwable
	 */
	public function getVendors(){
		return Vendor::findList();
	}
}
