<?php

namespace modules\customer\models;

use common\base\Status;
use common\models\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%customer_rank}}".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $type
 * @property int $status
 * @property int $is_default
 * @property int $daily_limit_balance
 * @property int $daily_count_balance
 * @property int $withdraw_limit_balance
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 *
 * @property Customer[] $customers
 * @property array $types
 * @property string $typeLabel
 */
class CustomerRank extends BaseActiveRecord{

	const IS_DEFAULT = 1;

	public static $alias = 'rank';

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%customer_rank}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['description', 'name'], 'required'],
			[['status', 'created_by', 'created_at', 'updated_by', 'updated_at', 'is_default', 'daily_count_balance', 'daily_limit_balance', 'withdraw_limit_balance'], 'integer'],
			[['name', 'type'], 'string', 'max' => 255],
			[['description'], 'string', 'max' => 1000],
		];
	}

	/**
	 * @return array
	 */
	public function behaviors(){
		$behaviors = parent::behaviors();
		unset($behaviors['status'], $behaviors['language']);
		$behaviors['audit']['module'] = Yii::t('customer', 'Player');

		return $behaviors;
	}

	/**
	 * @param bool $insert
	 * @param array $changedAttributes
	 */
	public function afterSave($insert, $changedAttributes){
		if (!empty($this->is_default) && $this->status == Status::STATUS_ACTIVE){
			self::updateAll(['is_default' => 0], ['<>', 'id', $this->id]);
		}

		parent::afterSave($insert, $changedAttributes);
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'                     => Yii::t('customer', 'ID'),
			'name'                   => Yii::t('customer', 'Rank Name'),
			'description'            => Yii::t('customer', 'Description'),
			'type'                   => Yii::t('customer', 'Type'),
			'status'                 => Yii::t('customer', 'Status'),
			'is_default'             => Yii::t('customer', 'Default rank'),
			'daily_count_balance'    => Yii::t('customer', 'Daily Limit Balance'),
			'daily_limit_balance'    => Yii::t('customer', 'Daily Count Balance'),
			'withdraw_limit_balance' => Yii::t('customer', 'Withdraw Limit Balance'),
			'created_by'             => Yii::t('customer', 'Created By'),
			'created_at'             => Yii::t('customer', 'Created at'),
			'updated_by'             => Yii::t('customer', 'Updated By'),
			'updated_at'             => Yii::t('customer', 'Updated At'),
		];
	}

	/**
	 * @return array
	 */
	public function getTypes(){
		return [
			'rock'     => Yii::t('customer', 'Rock'),
			'bronze'   => Yii::t('customer', 'Bronze'),
			'silver'   => Yii::t('customer', 'Silver'),
			'gold'     => Yii::t('customer', 'Gold'),
			'platinum' => Yii::t('customer', 'Platinum'),
			'diamond'  => Yii::t('customer', 'Diamond'),
			'ruby'     => Yii::t('customer', 'Ruby'),
		];
	}

	/**
	 * @return mixed|null
	 */
	public function getTypeLabel(){
		return $this->types[$this->type] ?? NULL;
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getCustomers(){
		return $this->hasMany(Customer::class, ['customer_classify_id' => 'id']);
	}

	/**
	 * @return false|int|string|null
	 */
	public static function default(){
		return self::find()
		           ->select('id')
		           ->andWhere(['status' => Status::STATUS_ACTIVE, 'is_default' => self::IS_DEFAULT])
		           ->scalar();
	}
}
