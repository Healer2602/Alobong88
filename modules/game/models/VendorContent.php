<?php

namespace modules\game\models;

use common\models\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%vendor_content}}".
 *
 * @property int $id
 * @property string $name
 * @property int $type_id
 * @property int $vendor_id
 * @property int $status
 * @property int $created_at
 * @property int $created_by
 * @property int $updated_at
 * @property int $updated_by
 *
 * @property GameType $type
 * @property Vendor $vendor
 * @property array $types
 * @property array $vendors
 * @property array $url
 */
class VendorContent extends BaseActiveRecord{

	public static $alias = 'vendor_content';

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%vendor_content}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['name'], 'required'],
			[['type_id', 'vendor_id', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
			[['name'], 'string', 'max' => 255],
			[['icon'], 'string'],
			[['type_id', 'vendor_id'], 'unique', 'targetAttribute' => ['type_id', 'vendor_id']],
			[['type_id'], 'exist', 'skipOnError' => TRUE, 'targetClass' => GameType::class, 'targetAttribute' => ['type_id' => 'id']],
			[['vendor_id'], 'exist', 'skipOnError' => TRUE, 'targetClass' => Vendor::class, 'targetAttribute' => ['vendor_id' => 'id']],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'         => Yii::t('game', 'ID'),
			'name'       => Yii::t('game', 'Name'),
			'icon'       => Yii::t('game', 'Icon'),
			'type_id'    => Yii::t('game', 'Game Type'),
			'vendor_id'  => Yii::t('game', 'Vendor'),
			'status'     => Yii::t('game', 'Status'),
			'created_at' => Yii::t('game', 'Created At'),
			'created_by' => Yii::t('game', 'Created By'),
			'updated_at' => Yii::t('game', 'Updated At'),
			'updated_by' => Yii::t('game', 'Updated By'),
		];
	}

	/**
	 * @return array
	 */
	public function behaviors(){
		$behaviors                    = parent::behaviors();
		$behaviors['audit']['module'] = Yii::t('game', 'Vendor Content');

		return $behaviors;
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
	 */
	public function getUrl(){
		return ['/game/type/index', 'slug' => $this->type->slug, 'partner' => $this->vendor->slug];
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