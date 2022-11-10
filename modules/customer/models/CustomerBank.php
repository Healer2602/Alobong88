<?php

namespace modules\customer\models;

use common\base\Status;
use modules\customer\frontend\models\CustomerIdentity;
use modules\wallet\models\Bank;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%customer_bank}}".
 *
 * @property int $id
 * @property int $customer_id
 * @property int $bank_id
 * @property string $account_id
 * @property string $account_name
 * @property string $account_branch
 *
 * @property Bank $bank
 * @property array $banks
 * @property Customer $customer
 */
class CustomerBank extends ActiveRecord{

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%customer_bank}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['customer_id', 'bank_id', 'account_id', 'account_name', 'account_branch'], 'required'],
			[['customer_id', 'bank_id'], 'integer'],
			[['account_id', 'account_name', 'account_branch'], 'string'],
			[['bank_id'], 'exist', 'skipOnError' => TRUE, 'targetClass' => Bank::class, 'targetAttribute' => ['bank_id' => 'id']],
			[['customer_id'], 'exist', 'skipOnError' => TRUE, 'targetClass' => Customer::class, 'targetAttribute' => ['customer_id' => 'id']],
			['account_name', 'validationBank']
		];
	}

	/**
	 * @param $attribute
	 *
	 * @return void
	 */
	public function validationBank($attribute){
		if ($this->account_name != CustomerIdentity::profile()->name){
			$this->addError($attribute,
				Yii::t('customer',
					'The name of the bank account does not match the registered full name, please contact Customer Care for assistance'));
		}
	}

	/**
	 * @return array
	 */
	public function behaviors(){
		$behaviors = parent::behaviors();
		unset($behaviors['language'], $behaviors['status']);

		return $behaviors;
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'             => Yii::t('common', 'ID'),
			'customer_id'    => Yii::t('customer', 'Customer ID'),
			'bank_id'        => Yii::t('customer', 'Bank ID'),
			'account_id'     => Yii::t('customer', 'Account No'),
			'bank'           => Yii::t('customer', 'Bank'),
			'account_name'   => Yii::t('customer', 'Account Holder'),
			'account_branch' => Yii::t('customer', 'Bank Branch'),
		];
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getBank(){
		return $this->hasOne(Bank::class, ['id' => 'bank_id']);
	}

	/**
	 * @return array
	 * @throws \Throwable
	 */
	public function getBanks(){
		$bank_ids = CustomerIdentity::profile()->bankIds ?? NULL;

		return Bank::find()
		           ->andWhere(['status' => Status::STATUS_ACTIVE])
		           ->andWhere(['not in', 'id', $bank_ids])
		           ->select(['name', 'id'])
		           ->indexBy('id')
		           ->column();
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getCustomer(){
		return $this->hasOne(Customer::class, ['id' => 'customer_id']);
	}
}