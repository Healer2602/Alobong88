<?php

namespace modules\wallet\models;

use modules\customer\models\Customer;
use modules\game\models\ProductWallet;
use Yii;
use yii\base\InvalidArgumentException;
use yii\bootstrap5\Html;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%wallet}}".
 *
 * @property int $id
 * @property int $customer_id
 * @property double $balance
 * @property double $turnover
 * @property double $total_deposit
 * @property double $previous_balance
 * @property int $last_update
 * @property string $verify_hash
 * @property int $status
 * @property boolean $auto_transfer
 *
 * @property Transaction[] $transactions
 * @property \modules\wallet\models\WalletSub[] $subWallets
 * @property Customer $customer
 * @property string $statusHtml
 * @property string $formattedBalance
 * @property \modules\wallet\models\Report $report
 * @property-read array $allWallets
 */
class Wallet extends ActiveRecord{

	const STATUS_LOCKED = 20;

	const STATUS_FRAUD = 15;

	const STATUS_ACTIVE = 10;

	const MAIN_WALLET = 'main';

	public $balance_subwallet = 0;
	public $balance_total = 0;

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%wallet}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['customer_id'], 'required'],
			[['customer_id', 'last_update', 'status'], 'integer'],
			[['balance', 'previous_balance', 'turnover'], 'number'],
			['auto_transfer', 'boolean'],
			['auto_transfer', 'default', 'value' => 0],
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
			$this->last_update      = time();
			$this->previous_balance = $this->getOldAttribute('balance') ?? 0;
		}

		if ($this->status == self::STATUS_ACTIVE){
			$this->verify_hash = Yii::$app->security->generatePasswordHash(strval($this->balance));
		}

		return parent::beforeSave($insert);
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'                => Yii::t('wallet', 'ID'),
			'customer.name'     => Yii::t('wallet', 'Player'),
			'customer_id'       => Yii::t('wallet', 'Player'),
			'balance'           => Yii::t('wallet', 'Balance'),
			'balance_subwallet' => Yii::t('wallet', 'Sub-Wallet'),
			'balance_total'     => Yii::t('wallet', 'Balance'),
			'previous_balance'  => Yii::t('wallet', 'Previous Balance'),
			'turnover'          => Yii::t('wallet', 'Turnover'),
			'last_update'       => Yii::t('wallet', 'Latest Transaction'),
			'status'            => Yii::t('common', 'Status'),
		];
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
	public function getTransactions(){
		return $this->hasMany(Transaction::class, ['wallet_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getSubWallets(){
		return $this->hasMany(WalletSub::class, ['wallet_id' => 'id']);
	}

	/**
	 * @return \modules\wallet\models\Report
	 */
	public function getReport(){
		return new Report(['wallet_id' => $this->id]);
	}

	/**
	 * @return bool
	 */
	public function verify(){
		try{
			return Yii::$app->security->validatePassword(strval($this->balance),
				$this->verify_hash);
		}catch (InvalidArgumentException $exception){
			return FALSE;
		}
	}

	/**
	 * @return bool
	 */
	public function isActive(){
		return $this->status == self::STATUS_ACTIVE;
	}


	/**
	 * @return bool
	 */
	public function setAsFraud(){
		$this->status = self::STATUS_FRAUD;

		return $this->save(FALSE);
	}

	/**
	 * @return array
	 */
	public static function statuses(){
		return [
			self::STATUS_ACTIVE => Yii::t('wallet', 'Active'),
			self::STATUS_LOCKED => Yii::t('wallet', 'Locked'),
			self::STATUS_FRAUD  => Yii::t('wallet', 'Fraud'),
		];
	}

	/**
	 * @return string|null
	 */
	public function getStatusHtml(){
		$label = self::statuses()[$this->status] ?? NULL;
		if ($this->status == self::STATUS_ACTIVE){
			return Html::tag('span', $label, ['class' => 'badge badge-success']);
		}

		if ($this->status == self::STATUS_LOCKED){
			return Html::tag('span', $label, ['class' => 'badge badge-danger']);
		}

		if ($this->status == self::STATUS_FRAUD){
			return Html::tag('span', $label, ['class' => 'badge badge-warning']);
		}

		return NULL;
	}

	/**
	 * @return string
	 * @throws \yii\base\InvalidConfigException
	 */
	public function getFormattedBalance(){
		return Yii::$app->formatter->asCurrency($this->balance);
	}

	/**
	 * @param $amount
	 *
	 * @return int
	 */
	public function updateTurnover($amount){
		return self::updateAllCounters(['turnover' => $amount], ['id' => $this->id]);
	}

	/**
	 * @param $amount
	 *
	 * @return int
	 */
	public function updateDeposit($amount){
		return self::updateAllCounters(['total_deposit' => $amount], ['id' => $this->id]);
	}

	/**
	 * @param $player_id
	 * @param $amount
	 *
	 * @return false|int
	 */
	public static function storeTurnover($player_id, $amount){
		$id = Customer::find()->select('id')->andWhere(['username' => $player_id])->scalar();
		if (!empty($id)){
			return self::updateAllCounters(['turnover' => $amount], ['id' => $id]);
		}

		return FALSE;
	}

	/**
	 * @return string
	 */
	public function getCustomerDetail(){
		if (empty($this->customer->name)){
			return NULL;
		}

		if (Yii::$app->user->can('wallet detail')){
			return Html::a($this->customer->name,
				['/wallet/default/view', 'id' => $this->id]);
		}

		return $this->customer->name;
	}

	/**
	 * @return \modules\wallet\models\Wallet|null
	 */
	public static function my(){
		return Yii::$app->user->identity->wallet ?? NULL;
	}

	private $_all_wallets = NULL;

	/**
	 * @return array
	 */
	public function getAllWallets(){
		if ($this->_all_wallets === NULL){
			$products = ProductWallet::find()
			                         ->default()
			                         ->select(['name', 'code'])
			                         ->indexBy('code')
			                         ->column();

			$this->_all_wallets = ArrayHelper::merge([self::MAIN_WALLET => Yii::t('wallet',
				'Main Wallet')],
				$products) ?? [];
		}

		return $this->_all_wallets;
	}
}
