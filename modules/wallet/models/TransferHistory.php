<?php

namespace modules\wallet\models;

use modules\customer\models\Customer;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%wallet_transfer}}".
 *
 * @property int $id
 * @property string $transaction_id
 * @property int $from
 * @property int $to
 * @property double $amount
 * @property int $customer_id
 * @property int $created_at
 *
 * @property Customer $customer
 * @property WalletSub $fromWallet
 * @property WalletSub $toWallet
 * @property string $fromName
 * @property string $toName
 */
class TransferHistory extends ActiveRecord{

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%wallet_transfer}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['from', 'to', 'customer_id', 'created_at'], 'integer'],
			[['amount'], 'number'],
			[['customer_id'], 'exist', 'skipOnError' => TRUE, 'targetClass' => Customer::class, 'targetAttribute' => ['customer_id' => 'id']],
			[['from'], 'exist', 'skipOnError' => TRUE, 'targetClass' => WalletSub::class, 'targetAttribute' => ['from' => 'id']],
			[['to'], 'exist', 'skipOnError' => TRUE, 'targetClass' => WalletSub::class, 'targetAttribute' => ['to' => 'id']],
			['transaction_id', 'string']
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'          => Yii::t('wallet', 'ID'),
			'from'        => Yii::t('wallet', 'From'),
			'to'          => Yii::t('wallet', 'To'),
			'amount'      => Yii::t('wallet', 'Amount'),
			'customer_id' => Yii::t('wallet', 'Player'),
			'created_at'  => Yii::t('wallet', 'Transaction Date'),
		];
	}

	/**
	 * @param $insert
	 *
	 * @return bool
	 */
	public function beforeSave($insert){
		if ($insert){
			$this->created_at = time();
		}

		return parent::beforeSave($insert);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getCustomer(){
		return $this->hasOne(Customer::class, ['id' => 'customer_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getFromWallet(){
		return $this->hasOne(WalletSub::class, ['id' => 'from']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getToWallet(){
		return $this->hasOne(WalletSub::class, ['id' => 'to']);
	}

	/**
	 * @return string
	 */
	public function getFromName(){
		if ($this->fromWallet){
			return $this->fromWallet->product->name ?? '';
		}

		return Yii::t('wallet', 'Main Wallet');
	}

	/**
	 * @return string
	 */
	public function getToName(){
		if ($this->toWallet){
			return $this->toWallet->product->name ?? '';
		}

		return Yii::t('wallet', 'Main Wallet');
	}

	/**
	 * @return array
	 */
	public static function players(){
		$customers = self::find()
		                 ->with('customer')
		                 ->distinct()
		                 ->asArray()
		                 ->all();

		return ArrayHelper::map($customers, 'customer_id', 'customer.name');
	}
}