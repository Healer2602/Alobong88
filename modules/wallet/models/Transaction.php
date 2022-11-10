<?php

namespace modules\wallet\models;

use common\base\EnvHelper;
use common\models\AuditTrail;
use modules\game\models\ProductWallet;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\bootstrap5\Html;
use yii\caching\DbDependency;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%wallet_transaction}}".
 *
 * @property int $id
 * @property int $wallet_id
 * @property int $type
 * @property double $amount
 * @property double $balance
 * @property double $fee
 * @property int $status
 * @property string $description
 * @property string $note
 * @property string $reference_id
 * @property string $transaction_id
 * @property string|array $params
 * @property int $created_at
 * @property int $updated_at
 * @property string $gateway_id
 * @property string $currency
 *
 * @property Wallet $wallet
 * @property \modules\wallet\models\Gateway $gateway
 * @property-read string $statusHtml
 * @property-read string $typeHtml
 * @property-read string $customerName
 * @property-read string $typeLabel
 * @property-read string $amountDetail
 * @property-read string $amountHtml
 */
class Transaction extends ActiveRecord{

	const TYPE_TOPUP = 10;

	const TYPE_RETURN = 12;

	const TYPE_RECEIVE = 14;

	const TYPE_REWARD = 36;

	// Minus money

	const TYPE_WITHDRAW = 40;

	const TYPE_TRANSFER = 42;

	const TYPE_PLAY = 44;

	// Status

	const STATUS_PENDING = 0;

	const STATUS_PROCESSING = 5;

	const STATUS_SUCCESS = 10;

	const STATUS_CANCELED = 15;

	const STATUS_FAILED = 20;

	const STATUS_REJECTED = 25;


	const SCENARIO_OPERATOR = 'operator';

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%wallet_transaction}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['wallet_id', 'type', 'amount'], 'required'],
			[['wallet_id', 'type', 'status', 'created_at', 'updated_at', 'gateway_id'], 'integer'],
			[['amount', 'balance', 'fee'], 'number'],
			[['params'], 'safe'],
			[['reference_id', 'transaction_id'], 'string', 'max' => 255],
			[['wallet_id'], 'exist', 'skipOnError' => TRUE, 'targetClass' => Wallet::class, 'targetAttribute' => ['wallet_id' => 'id']],
			['status', 'default', 'value' => self::STATUS_PENDING],
			['fee', 'default', 'value' => 0],
			[['description', 'note'], 'string'],
			['note', 'required', 'on' => self::SCENARIO_OPERATOR],
			[['currency'], 'string']
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
	 * @return array
	 */
	public function behaviors(){
		return [
			'timestamp' => TimestampBehavior::class
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'             => Yii::t('wallet', 'Transaction ID'),
			'wallet_id'      => Yii::t('wallet', 'Wallet'),
			'type'           => Yii::t('wallet', 'Transaction Type'),
			'amount'         => Yii::t('wallet', 'Amount'),
			'balance'        => Yii::t('wallet', 'Balance'),
			'description'    => Yii::t('wallet', 'Description'),
			'note'           => Yii::t('wallet', 'Note'),
			'fee'            => Yii::t('wallet', 'Fee'),
			'reference_id'   => Yii::t('wallet', 'Reference Code'),
			'params'         => Yii::t('wallet', 'Params'),
			'created_at'     => Yii::t('wallet', 'Transaction Date'),
			'status'         => Yii::t('common', 'Status'),
			'customer.name'  => Yii::t('wallet', 'Player'),
			'transaction_id' => Yii::t('wallet', 'Transaction ID'),
			'gateway_id'     => Yii::t('wallet', 'Gateway'),
			'currency'       => Yii::t('wallet', 'Currency'),
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
				[self::TYPE_WITHDRAW, self::TYPE_TRANSFER])){
				$this->amount = abs($this->amount) * - 1;
			}
		}

		return parent::beforeSave($insert);
	}

	/**
	 * @param bool $insert
	 * @param array $changedAttributes
	 *
	 * @throws \yii\base\InvalidConfigException
	 */
	public function afterSave($insert, $changedAttributes){
		if (!$insert && isset($changedAttributes['status'])){
			self::log($this->type, $this->status, $changedAttributes['status']);

			if ($this->status == self::STATUS_SUCCESS){
				if ($this->type == self::TYPE_WITHDRAW){
					Notification::withdrawNew($this);
				}elseif ($this->type == self::TYPE_TOPUP){
					Notification::newDeposit($this);
				}
			}
		}

		if ($insert){
			if ($this->status == self::STATUS_SUCCESS){
				if ($this->type == self::TYPE_WITHDRAW){
					Notification::withdrawNew($this);
				}
			}
		}

		if ($this->type == self::TYPE_TOPUP && isset($changedAttributes['status']) && $this->status == self::STATUS_SUCCESS && $this->status != $changedAttributes['status']){
			$this->wallet->updateDeposit($this->amount);
		}

		if ($this->type == self::TYPE_WITHDRAW && $this->status == self::STATUS_PENDING && ($insert || (isset($changedAttributes['status']) && $this->status != $changedAttributes['status']))){
			Notification::telegramWithdrawNew($this);
		}

		parent::afterSave($insert, $changedAttributes);
	}

	/**
	 * @inheritDoc
	 */
	public function afterFind(){
		if (is_string($this->params)){
			$this->params = Json::decode($this->params);
		}

		parent::afterFind();
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
	public function getGateway(){
		return $this->hasOne(Gateway::class, ['id' => 'gateway_id']);
	}

	/**
	 * @return array
	 */
	public static function statuses(){
		return [
			self::STATUS_PROCESSING => Yii::t('wallet', 'Processing'),
			self::STATUS_PENDING    => Yii::t('wallet', 'Pending'),
			self::STATUS_SUCCESS    => Yii::t('wallet', 'Success'),
			self::STATUS_CANCELED   => Yii::t('wallet', 'Canceled'),
			self::STATUS_FAILED     => Yii::t('wallet', 'Failed'),
			self::STATUS_REJECTED   => Yii::t('wallet', 'Rejected'),
		];
	}

	/**
	 * @return array
	 */
	public static function types(){
		return [
			self::TYPE_TOPUP    => Yii::t('wallet', 'Deposit'),
			self::TYPE_WITHDRAW => Yii::t('wallet', 'Withdraw'),
			self::TYPE_TRANSFER => Yii::t('wallet', 'Transfer'),
			self::TYPE_RECEIVE  => Yii::t('wallet', 'Receive'),
			self::TYPE_REWARD   => Yii::t('wallet', 'Reward'),
			self::TYPE_PLAY     => Yii::t('wallet', 'Game Withdraw'),
			self::TYPE_RETURN   => Yii::t('wallet', 'Return'),
		];
	}

	/**
	 * @return string|null
	 */
	public static function statusHtml($status){
		$label = self::statuses()[$status] ?? NULL;

		if ($status == self::STATUS_PENDING || $status == self::STATUS_PROCESSING){
			return Html::tag('span', $label, ['class' => 'badge bg-warning']);
		}

		if ($status == self::STATUS_SUCCESS){
			return Html::tag('span', $label, ['class' => 'badge bg-success']);
		}

		if ($status == self::STATUS_FAILED || $status == self::STATUS_CANCELED || $status == self::STATUS_REJECTED){
			return Html::tag('span', $label, ['class' => 'badge bg-danger']);
		}

		if (!empty($label)){
			return Html::tag('span', $label, ['class' => 'badge bg-secondary']);
		}

		return NULL;
	}

	/**
	 * @return string|null
	 */
	public static function typeHtml($type){
		$label = self::types()[$type] ?? NULL;
		if ($type == self::TYPE_TRANSFER){
			return Html::tag('span', $label, ['class' => 'badge bg-warning-soft']);
		}

		if ($type == self::TYPE_TOPUP || $type == self::TYPE_RETURN || $type == self::TYPE_RECEIVE){
			return Html::tag('span', $label, ['class' => 'badge bg-success-soft']);
		}

		if ($type == self::TYPE_WITHDRAW){
			return Html::tag('span', $label, ['class' => 'badge bg-danger-soft']);
		}

		if ($type == self::TYPE_REWARD){
			return Html::tag('span', $label, ['class' => 'badge bg-primary-soft']);
		}

		if (!empty($label)){
			return Html::tag('span', $label, ['class' => 'badge bg-info-soft']);
		}

		return NULL;
	}

	/**
	 * @return string|null
	 */
	public function getStatusHtml(){
		return self::statusHtml($this->status);
	}

	/**
	 * @return string|null
	 */
	public function getTypeHtml(){
		return self::typeHtml($this->type);
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
	 * @return mixed|null
	 */
	public function getTypeLabel(){
		return self::types()[$this->type] ?? NULL;
	}

	/**
	 * @return array
	 */
	public static function customers(){
		$customers = Wallet::find()
		                   ->with('customer')
		                   ->distinct()
		                   ->asArray()
		                   ->all();

		return ArrayHelper::map($customers, 'customer_id', 'customer.name');
	}

	/**
	 * @return string
	 * @throws \yii\base\InvalidConfigException
	 */
	public function getAmountDetail(){
		return Yii::$app->formatter->asCurrency($this->amount);
	}

	/**
	 * @return string
	 */
	public function getAmountHtml(){
		if ($this->amount < 0){
			return Html::tag('strong', $this->amountDetail, ['class' => 'text-danger']);
		}

		return Html::tag('strong', $this->amountDetail, ['class' => 'text-success']);
	}

	/**
	 * @return mixed|string|null
	 */
	public function formatParams(){
		if (is_string($this->params)){
			return Json::decode($this->params);
		}

		return $this->params;
	}

	/**
	 * @param $action
	 * @param $status
	 * @param null $old_status
	 *
	 * @return bool
	 */
	public static function log($action, $status, $old_status = NULL){
		if (!empty($old_status) && !empty($status)){
			$status_label     = self::statuses()[$status] ?? NULL;
			$old_status_label = self::statuses()[$old_status] ?? NULL;

			$message = Yii::t('wallet', 'Update transaction status from {0} to {1}', [
				$old_status_label, $status_label
			]);

			$label = self::types()[$action] ?? NULL;

			return AuditTrail::log($label, $message, 'eWallet', NULL);
		}

		return FALSE;
	}

	/**
	 * @return bool
	 */
	public function needApproval()
	: bool{
		if (!Yii::$app->user->can('wallet transaction approve')){
			return FALSE;
		}

		if ($this->status == self::STATUS_PENDING && $this->type == self::TYPE_WITHDRAW){
			return TRUE;
		}

		if ($this->status == self::STATUS_PROCESSING && $this->type == self::TYPE_TOPUP){
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return bool
	 */
	public function canReturn()
	: bool{
		if (!Yii::$app->user->can('wallet transaction return')){
			return FALSE;
		}

		if ($this->type != self::TYPE_WITHDRAW){
			return FALSE;
		}

		if ($this->status == self::STATUS_SUCCESS){
			return FALSE;
		}

		$params  = $this->formatParams();
		$gateway = Gateway::findOne(['key' => $params['Gateway ID'] ?? NULL]);
		if (!empty($gateway) && !empty($gateway->gateway->config['external_withdraw'])){
			$ref = Transaction::findOne(['reference_id' => $this->transaction_id, 'status' => self::STATUS_SUCCESS]);
			if (empty($ref)){
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * @return bool
	 */
	public function canUpdateStatus(){
		if ($this->type != self::TYPE_TOPUP){
			return FALSE;
		}

		if (!Yii::$app->user->can('wallet transaction update')){
			return FALSE;
		}

		$has_updated = self::find()->andWhere([
			'reference_id' => $this->transaction_id,
			'type'         => self::TYPE_TOPUP,
			'status'       => self::STATUS_SUCCESS
		])->exists();

		if ($has_updated){
			return FALSE;
		}

		$params = $this->formatParams();

		if (!empty($params['receive_amount']) && $this->status != self::STATUS_SUCCESS && $this->status != self::STATUS_CANCELED){
			return TRUE;
		}

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

		if ($this->type != self::TYPE_TRANSFER){
			return FALSE;
		}

		if ($this->created_at <= strtotime("-14days")){
			return FALSE;
		}

		$ref = self::find()
		           ->andWhere(['reference_id' => $this->transaction_id, 'status' => self::STATUS_SUCCESS, 'type' => Transaction::TYPE_RETURN])
		           ->exists();

		if (!empty($ref)){
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * @param array $data
	 *
	 * @return bool|\modules\wallet\models\Transaction
	 */
	public static function store($data)
	: ?Transaction{
		$model = new self();
		$model->setAttributes($data);

		if ($model->save()){
			return $model;
		}

		return NULL;
	}

	/**
	 * @return array
	 * @throws \Exception
	 */
	public static function wallets(){
		return WalletSub::getDb()->cache(function (){
			$products = ProductWallet::find()
			                         ->select(['name', 'code'])
			                         ->indexBy('code')
			                         ->column();

			return ArrayHelper::merge([Wallet::MAIN_WALLET => Yii::t('wallet',
				'Main Wallet')],
				$products) ?? [];
		}, 0, new DbDependency([
			'sql' => ProductWallet::find()->select(['MAX(updated_at)'])->createCommand()->rawSql
		]));
	}
}
