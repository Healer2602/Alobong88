<?php

namespace modules\wallet\models;

use modules\customer\models\Customer;
use modules\game\models\ProductWallet;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%wallet_sub}}".
 *
 * @property int $id
 * @property int $wallet_id
 * @property int $player_id
 * @property string $product_code
 * @property double $balance
 * @property int $last_update
 * @property string $verify_hash
 * @property int $status
 *
 * @property Wallet $wallet
 * @property ProductWallet $product
 * @property WalletSubTransaction[] $transactions
 * @property Customer $customer
 * @property \modules\promotion\models\Promotion $promotion
 */
class WalletSub extends ActiveRecord{

	const STATUS_ACTIVE = 10;

	const STATUS_LOCKED = 20;

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%wallet_sub}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['wallet_id', 'product_code', 'verify_hash', 'player_id'], 'required'],
			[['wallet_id', 'last_update', 'status', 'player_id'], 'integer'],
			[['balance'], 'number'],
			[['product_code', 'verify_hash'], 'string', 'max' => 255],
			[['wallet_id', 'product_code'], 'unique', 'targetAttribute' => ['wallet_id', 'product_code']],
			[['wallet_id'], 'exist', 'skipOnError' => TRUE, 'targetClass' => Wallet::class, 'targetAttribute' => ['wallet_id' => 'id']],
			[['player_id'], 'exist', 'skipOnError' => TRUE, 'targetClass' => Customer::class, 'targetAttribute' => ['player_id' => 'id']],
			['balance', 'default', 'value' => 0],
			['status', 'default', 'value' => self::STATUS_ACTIVE],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'           => Yii::t('wallet', 'ID'),
			'wallet_id'    => Yii::t('wallet', 'Wallet ID'),
			'player_id'    => Yii::t('wallet', 'Player'),
			'product_code' => Yii::t('wallet', 'Product Code'),
			'balance'      => Yii::t('wallet', 'Balance'),
			'last_update'  => Yii::t('wallet', 'Last Update'),
			'verify_hash'  => Yii::t('wallet', 'Verify Hash'),
			'status' => Yii::t('wallet', 'Status')
		];
	}

	/**
	 * @param bool $insert
	 *
	 * @return bool
	 * @throws \yii\base\Exception
	 */
	public function beforeSave($insert){
		if ($this->getOldAttribute('balance') <> $this->balance){
			$this->last_update = time();
			$this->verify_hash = Yii::$app->security->generatePasswordHash(strval($this->balance));
		}

		return parent::beforeSave($insert);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getWallet(){
		return $this->hasOne(Wallet::class, ['id' => 'wallet_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getProduct(){
		return $this->hasOne(ProductWallet::class, ['code' => 'product_code']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getTransactions(){
		return $this->hasMany(WalletSubTransaction::class, ['wallet_sub_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getCustomer(){
		return $this->hasOne(Customer::class, ['id' => 'player_id']);
	}

	/**
	 * @return array
	 */
	public static function statuses(){
		return [
			self::STATUS_ACTIVE => Yii::t('wallet', 'Active'),
			self::STATUS_LOCKED => Yii::t('wallet', 'Locked'),
		];
	}

	/**
	 * @return mixed|null
	 */
	public function getStatusLabel(){
		return self::statuses()[$this->status] ?? NULL;
	}
}