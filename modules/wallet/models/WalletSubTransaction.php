<?php

namespace modules\wallet\models;

use common\base\EnvHelper;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%wallet_sub_transaction}}".
 *
 * @property int $id
 * @property int $wallet_sub_id
 * @property string $transaction_id
 * @property int $type
 * @property double $amount
 * @property double $fee
 * @property double $balance
 * @property string $currency
 * @property string $description
 * @property string $note
 * @property int $status
 * @property string $reference_id
 * @property string $params
 * @property int $created_at
 * @property int $updated_at
 *
 * @property WalletSub $wallet
 * @property-read string $statusHtml
 * @property-read string $typeHtml
 */
class WalletSubTransaction extends ActiveRecord{

	const SCENARIO_OPERATOR = 'operator';

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%wallet_sub_transaction}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['wallet_sub_id', 'type', 'amount'], 'required'],
			[['wallet_sub_id', 'type', 'status', 'created_at', 'updated_at'], 'integer'],
			[['amount', 'fee', 'balance'], 'number'],
			[['description', 'note', 'params'], 'string'],
			[['transaction_id', 'currency', 'reference_id'], 'string', 'max' => 255],
			[['wallet_sub_id'], 'exist', 'skipOnError' => TRUE, 'targetClass' => WalletSub::class, 'targetAttribute' => ['wallet_sub_id' => 'id']],
			['note', 'required', 'on' => self::SCENARIO_OPERATOR],
		];
	}

	/**
	 * @return array
	 */
	public function scenarios(){
		$scenarios = parent::scenarios();

		$scenarios[self::SCENARIO_OPERATOR] = ['note'];

		return $scenarios;
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'             => Yii::t('wallet', 'ID'),
			'wallet_sub_id'  => Yii::t('wallet', 'Wallet Sub ID'),
			'transaction_id' => Yii::t('wallet', 'Transaction ID'),
			'type'           => Yii::t('wallet', 'Type'),
			'amount'         => Yii::t('wallet', 'Amount'),
			'fee'            => Yii::t('wallet', 'Fee'),
			'balance'        => Yii::t('wallet', 'Balance'),
			'currency'       => Yii::t('wallet', 'Currency'),
			'description'    => Yii::t('wallet', 'Description'),
			'note'           => Yii::t('wallet', 'Note'),
			'status'         => Yii::t('wallet', 'Status'),
			'reference_id'   => Yii::t('wallet', 'Reference ID'),
			'params'         => Yii::t('wallet', 'Params'),
			'created_at'     => Yii::t('wallet', 'Transaction Date'),
			'updated_at'     => Yii::t('wallet', 'Updated At'),
			'customer.name'  => Yii::t('wallet', 'Player'),
		];
	}

	/**
	 * @return array
	 */
	public function behaviors(){
		return [
			'timestamp' => TimestampBehavior::class
		];
	}

	/**
	 * @param bool $insert
	 *
	 * @return bool
	 */
	public function beforeSave($insert){
		if (is_array($this->params)){
			$this->params = Json::encode($this->params);
		}

		if ($insert){
			$this->transaction_id = strtoupper(uniqid(EnvHelper::env('TRANSACTION_PREFIX')));
			$this->balance        = $this->wallet->balance ?? 0;

			if (ArrayHelper::isIn($this->type,
				[Transaction::TYPE_WITHDRAW, Transaction::TYPE_TRANSFER])){
				$this->amount = abs($this->amount) * - 1;
			}
		}

		return parent::beforeSave($insert);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getSubWallet(){
		return $this->hasOne(WalletSub::class, ['id' => 'wallet_sub_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getWallet(){
		return $this->hasOne(WalletSub::class, ['id' => 'wallet_sub_id']);
	}

	/**
	 * @param array $data
	 *
	 * @return bool|\modules\wallet\models\WalletSubTransaction
	 */
	public static function store($data)
	: ?WalletSubTransaction{
		$model = new self();
		$model->setAttributes($data);

		if ($model->save()){
			return $model;
		}

		return NULL;
	}

	/**
	 * @return string|null
	 */
	public function getStatusHtml(){
		return Transaction::statusHtml($this->status);
	}

	/**
	 * @return string|null
	 */
	public function getTypeHtml(){
		return Transaction::typeHtml($this->type);
	}

	/**
	 * @return mixed|null
	 */
	public function getTypeLabel(){
		return Transaction::types()[$this->type] ?? NULL;
	}

	/**
	 * @return string|null
	 */
	public function getCustomerName(){
		if ($customer = $this->wallet->customer){
			if ($customer->name && $customer->email){
				return $customer->name . ' (' . $customer->email . ')';
			}

			return $customer->email ?? NULL;
		}

		return NULL;
	}

	/**
	 * @return false
	 */
	public function canUpdateStatus(){
		return FALSE;
	}

	/**
	 * @return false
	 */
	public function canReturn(){
		return FALSE;
	}

	/**
	 * @return false
	 */
	public function needApproval(){
		return FALSE;
	}

	/**
	 * @return bool
	 */
	public function canReturnTransfer()
	: bool{
		if (!Yii::$app->user->can('wallet transaction return')){
			return FALSE;
		}

		if ($this->type != Transaction::TYPE_TRANSFER){
			return FALSE;
		}

		if ($this->created_at <= strtotime("-14days")){
			return FALSE;
		}

		$ref = Transaction::find()
		                  ->andWhere(['reference_id' => $this->transaction_id, 'status' => Transaction::STATUS_SUCCESS, 'type' => Transaction::TYPE_RETURN])
		                  ->exists();

		if (!empty($ref)){
			return FALSE;
		}

		return TRUE;
	}
}