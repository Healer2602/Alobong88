<?php

namespace modules\customer\models;

use common\base\AppHelper;
use common\base\Status;
use common\models\BaseActiveRecord;
use common\models\Country;
use common\models\UserLanguage;
use modules\agent\models\Agent;
use modules\matrix\base\Player;
use modules\wallet\models\Bank;
use modules\wallet\models\Wallet;
use Yii;
use yii\base\InvalidArgumentException;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%customer}}".
 *
 * @property int $id
 * @property int $customer_rank_id
 * @property string $name
 * @property string $username
 * @property string $email
 * @property string $phone_number
 * @property string $dob
 * @property string $gender
 * @property string|\yii\web\UploadedFile $avatar
 * @property string $ip_address
 * @property string $country_code
 * @property string $currency
 * @property boolean $has_account
 * @property string|array $verify
 * @property int $status
 * @property int $referral_id
 * @property int $agent_id
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 * @property int $auth_key
 * @property int $password_hash
 * @property int $password_reset_token
 *
 * @property CustomerRank $rank
 * @property CustomerBank $bank
 * @property Kyc $kyc
 * @property array $ranks
 * @property array $banks
 * @property array $statuses
 * @property array $genders
 * @property string $avatarImage
 * @property bool $isVerified
 * @property bool $isEmailVerified
 * @property-read  \modules\customer\models\CustomerAuth $auth
 * @property-read  Wallet $wallet
 * @property-read  string $country
 * @property-read  string $shortName
 * @property-read  string $nameWE
 * @property-read  Referral $referral
 * @property-read  \modules\agent\models\Agent $agent
 * @property-read  \modules\customer\models\Customer $referralCustomer
 * @property-read  array $currencies
 * @property-read  \modules\customer\models\Setting $setting
 * @property-read  string $playPass
 * @property-read  array $bankIds
 * @property-read  UserLanguage $language
 */
class Customer extends BaseActiveRecord{

	const SCENARIO_CREATE = 'create';

	const SCENARIO_INVITE = 'invite';

	public static $alias = 'customer';

	public $password;
	public $confirm_password;

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%customer}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['customer_rank_id', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
			[['username', 'email', 'name'], 'required', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_DEFAULT]],
			[['username', 'email'], 'unique'],
			[['name', 'username', 'email', 'phone_number', 'dob', 'gender', 'ip_address', 'currency'], 'string', 'max' => 255],
			['email', 'email'],
			['email', 'required', 'on' => self::SCENARIO_INVITE],
			[['customer_rank_id'], 'exist', 'skipOnError' => TRUE, 'targetClass' => CustomerRank::class, 'targetAttribute' => ['customer_rank_id' => 'id']],

			['email', 'unique', 'message' => Yii::t('customer',
				'This email address has already been taken.')],
			['username', 'unique', 'message' => Yii::t('customer',
				'This username has already been taken.')],
			[['password', 'confirm_password'], 'string', 'min' => 6],
			['confirm_password', 'compare', 'compareAttribute' => 'password'],
			['password', 'compare', 'compareAttribute' => 'confirm_password'],
			['avatar', 'string'],
			[['country_code'], 'string', 'max' => 2],
			['referral_id', 'integer'],
			['has_account', 'boolean'],
			['has_account', 'default', 'value' => FALSE],
			['verify', 'safe']
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id' => Yii::t('common', 'ID'),

			'customer_rank_id' => Yii::t('customer', 'Rank'),
			'name'             => Yii::t('customer', 'Name'),
			'username'         => Yii::t('customer', 'Username'),
			'referral_id'      => Yii::t('customer', 'Referral'),
			'agent_id'         => Yii::t('customer', 'Agent'),
			'email'            => Yii::t('customer', 'Email'),
			'phone_number'     => Yii::t('customer', 'Phone Number'),
			'dob'              => Yii::t('customer', 'Date of Birth'),
			'gender'           => Yii::t('customer', 'Gender'),
			'status'           => Yii::t('customer', 'Activation'),
			'created_by'       => Yii::t('customer', 'Created By'),
			'created_at'       => Yii::t('customer', 'Joined At'),
			'updated_by'       => Yii::t('customer', 'Updated By'),
			'updated_at'       => Yii::t('customer', 'Updated At'),
			'ip_address'       => Yii::t('customer', 'IP Address'),
			'country_code'     => Yii::t('customer', 'Country'),
			'currency'         => Yii::t('customer', 'Currency'),
			'has_account'      => Yii::t('customer', 'Account'),
		];
	}

	/**
	 * @return array
	 */
	public function behaviors(){
		$behaviors = parent::behaviors();
		unset($behaviors['language']);
		$behaviors['audit']['module']   = Yii::t('common', 'Player');
		$behaviors['audit']['category'] = Yii::t('common', 'player');

		return $behaviors;
	}

	/**
	 * @return bool
	 */
	public function beforeDelete(){
		$this->deleteImage($this->avatar);
		CustomerAuth::deleteAll(['customer_id' => $this->id]);

		return parent::beforeDelete();
	}

	/**
	 * @param bool $insert
	 *
	 * @return bool
	 * @throws \yii\base\Exception
	 */
	public function beforeSave($insert){
		if ($insert){
			if ($this->scenario == self::SCENARIO_INVITE){
				$this->status = Status::STATUS_INACTIVE;
			}

			if (empty($this->password)){
				$this->password             = Yii::$app->security->generateRandomString(8);
				$this->auth_key             = Yii::$app->security->generateRandomString();
				$this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
			}

			$this->ip_address = Yii::$app->request->userIP;
		}

		if (!empty($this->password)){
			$this->password_hash = Yii::$app->security->generatePasswordHash($this->password);
			$this->auth_key      = Yii::$app->security->generateRandomString();
		}

		if (is_array($this->verify)){
			$this->verify = Json::encode($this->verify);
		}

		return parent::beforeSave($insert);
	}


	/**
	 * @param $insert
	 * @param $changedAttributes
	 *
	 * @return void
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\httpclient\Exception
	 */
	public function afterSave($insert, $changedAttributes){
		if (empty($this->wallet)){
			$this->setWallet();
		}

		if ($insert){
			$this->generateReferral();

			Notification::newCustomer($this);
		}

		if (empty($this->has_account)){
			Player::createAccount($this);
		}

		parent::afterSave($insert, $changedAttributes);
	}

	/**
	 * @return void
	 */
	public function afterFind(){
		parent::afterFind();

		try{
			$this->verify = Json::decode($this->verify);
		}catch (InvalidArgumentException $exception){
			$this->verify = [];
		}
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getRank(){
		return $this->hasOne(CustomerRank::class, ['id' => 'customer_rank_id']);
	}

	/**
	 * @return array
	 * @throws \Throwable
	 */
	public function getRanks(){
		return CustomerRank::findList();
	}

	/**
	 * @return array
	 */
	public function getGenders(){
		return [
			'Male'   => 'Male',
			'Female' => 'Female',
			'Other'  => 'Other'
		];
	}

	/**
	 * @return array
	 */
	public function getStatuses(){
		return [
			Status::STATUS_ACTIVE   => Yii::t('customer', 'Active'),
			Status::STATUS_INACTIVE => Yii::t('customer', 'Inactive')
		];
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getKyc(){
		return $this->hasOne(Kyc::class, ['customer_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getReferral(){
		return $this->hasOne(Referral::class, ['customer_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 * @throws \yii\base\InvalidConfigException
	 */
	public function getReferralCustomer(){
		return $this->hasOne(static::class, ['id' => 'customer_id'])
		            ->viaTable(Referral::tableName(), ['id' => 'referral_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getAgent(){
		return $this->hasOne(Agent::class, ['id' => 'agent_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getWallet(){
		return $this->hasOne(Wallet::class, ['customer_id' => 'id']);
	}

	/**
	 * @param $image
	 *
	 * @return null
	 */
	public function deleteImage($image){
		$image = AppHelper::uploadPath(self::$alias, basename($image));
		if (file_exists($image)){
			AppHelper::deleteFile($image);
		}

		return TRUE;
	}

	/**
	 * Upload avatar
	 */
	public function uploadAvatar($image){
		if (empty($image)){
			return FALSE;
		}

		[, $data] = explode(';', $image);
		[, $data] = explode(',', $data);
		$data = base64_decode($data);

		if (!empty($data)){
			if (!empty($this->avatar)){
				$this->deleteImage($this->avatar);
			}

			$file_name = time() . '.png';
			$file_path = AppHelper::uploadPath(self::$alias, $file_name);
			if (@file_put_contents($file_path, $data)){
				static::updateAll(['avatar' => $file_name], ['id' => $this->id]);
			}
		}

		return FALSE;
	}

	/**
	 * @return string|null
	 */
	public function getAvatarImage(){
		if (!empty($this->avatar)){
			return AppHelper::uploadUri(self::$alias, $this->avatar);
		}

		return NULL;
	}

	/**
	 * @return bool
	 */
	public function getIsVerified(){
		return !empty($this->kyc->status) && $this->kyc->status == Status::STATUS_ACTIVE;
	}

	/**
	 * @return bool
	 */
	public function getIsEmailVerified(){
		return !empty($this->verify['email']) && $this->verify['email'] === TRUE;
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getAuth(){
		return $this->hasOne(CustomerAuth::class, ['customer_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getLanguage(){
		return $this->hasOne(UserLanguage::class, ['user_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getBanks(){
		return $this->hasMany(CustomerBank::class, ['customer_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 * @throws \yii\base\InvalidConfigException
	 */
	public function getBank(){
		return $this->hasMany(Bank::class, ['id' => 'bank_id'])
		            ->viaTable(CustomerBank::tableName(), ['customer_id' => 'id']);
	}

	/**
	 * @return mixed|string|null
	 */
	public function getCountry(){
		return Country::list()[$this->country_code] ?? NULL;
	}

	/**
	 * @return bool
	 */
	private function generateReferral(){
		$referral = new Referral([
			'customer_id' => $this->id
		]);

		return $referral->save();
	}

	/**
	 * @inheritDoc
	 */
	public function setWallet(){
		$wallet = new Wallet([
			'customer_id'      => $this->id,
			'balance'          => 0,
			'previous_balance' => 0,
			'status'           => Wallet::STATUS_ACTIVE
		]);

		$wallet->save();
	}

	/**
	 * @return mixed|string
	 */
	public function getShortName(){
		$name = explode(" ", trim($this->name));

		return $name[0] ?? $this->name;
	}

	/**
	 * @return string
	 */
	public function getNameWE(){
		return $this->name . ' (' . $this->email . ')';
	}

	/**
	 * @return array
	 */
	public function getCurrencies(){
		return $this->setting->listCurrency ?? [];
	}

	/**
	 * @var null
	 */
	private $_setting = NULL;

	/**
	 * @return \modules\customer\models\Setting
	 * @throws \Throwable
	 */
	public function getSetting(){
		if ($this->_setting === NULL){
			$setting = new Setting();
			$setting->getValues();

			$this->_setting = $setting;
		}

		return $this->_setting;
	}

	/**
	 * @return string
	 */
	public function getPlayPass(){
		return md5($this->username . '!@#' . $this->email);
	}

	/**
	 * @return array
	 */
	public function getBankIds()
	: array{
		return CustomerBank::find()
		                   ->andWhere(['customer_id' => $this->id])
		                   ->select('bank_id')
		                   ->indexBy('bank_id')
		                   ->column();
	}
}