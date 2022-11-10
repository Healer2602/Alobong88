<?php

namespace modules\customer\frontend\models;

use DateTime;
use modules\agent\models\Agent;
use modules\customer\models\Customer;
use modules\customer\models\CustomerRank;
use modules\customer\models\Referral;
use modules\customer\models\Setting;
use modules\customer\Module;
use modules\spider\recaptcha\Validator;
use Yii;
use yii\base\Model;

/**
 * Register form
 *
 * @property-read Setting $setting
 * @property-read array $currencies
 */
class RegisterForm extends Model{

	const SCENARIO_SOCIAL = 'social';

	public $name;
	public $username;
	public $phone;
	public $email;
	public $password;
	public $confirm_password;
	public $captcha;
	public $currency;
	public $dob;

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['name', 'phone', 'currency'], 'string'],
			[['name', 'currency', 'phone', 'dob'], 'required'],
			['dob', 'date', 'format' => 'php:d/m/Y'],
			['dob', 'validateDOB'],

			['username', 'trim'],
			['username', 'required'],
			['username', 'unique', 'targetClass' => Customer::class, 'message' => Yii::t('common',
				'This username has already been taken.')],
			['username', 'string', 'min' => 2, 'max' => 255],

			['email', 'trim'],
			['email', 'required'],
			['email', 'email'],
			['email', 'string', 'max' => 255],
			['email', 'unique', 'targetClass' => Customer::class, 'message' => Yii::t('common',
				'This email address has already been taken.')],

			[['password', 'confirm_password'], 'required'],
			[['password', 'confirm_password'], 'string', 'min' => 8],
			['confirm_password', 'compare', 'compareAttribute' => 'password'],
			['captcha', Validator::class, 'on' => self::SCENARIO_DEFAULT],
		];
	}

	/**
	 * @return array|array[]
	 */
	public function scenarios(){
		$scenarios = parent::scenarios();

		$scenarios[self::SCENARIO_SOCIAL] = ['name', 'username', 'email', 'password', 'confirm_password'];

		return $scenarios;
	}

	/**
	 * @param $attribute
	 *
	 * @return void
	 */
	public function validateDOB($attribute){
		if (!empty($attribute)){
			$date = DateTime::createFromFormat('d/m/Y', $this->$attribute);
			if (empty($date) || $date->diff(new DateTime('now'))->y < 18){
				$this->addError($attribute, Yii::t('customer',
					'You need to be at least 18 years old to register an account.'));
			}
		}
	}

	/**
	 * @return array
	 */
	public function attributeLabels(){
		return [
			'name'             => Yii::t('customer', 'Fullname'),
			'username'         => Yii::t('customer', 'Username'),
			'phone'            => Yii::t('customer', 'Phone'),
			'password'         => Yii::t('customer', 'Password'),
			'confirm_password' => Yii::t('customer', 'Confirm Password'),
			'currency'         => Yii::t('customer', 'Currency'),
			'dob'              => Yii::t('customer', 'Date of birth'),
		];
	}

	/**
	 * Signs user up.
	 *
	 * @return CustomerIdentity|null the saved model or null if saving fails
	 * @throws \yii\base\Exception
	 */
	public function register(){
		if (!$this->validate()){
			return NULL;
		}

		$user               = new CustomerIdentity();
		$user->name         = $this->name;
		$user->username     = $this->username;
		$user->email        = $this->email;
		$user->phone_number = $this->phone;
		$user->currency     = $this->currency;
		$user->dob          = $this->dob;

		$user->password         = $this->password;
		$user->confirm_password = $this->password;
		if ($default_rank = CustomerRank::default()){
			$user->customer_rank_id = intval($default_rank);
		}

		if ($referral_code = \modules\agent\Module::getRef()){
			$referral       = Agent::findByCode($referral_code);
			$user->agent_id = $referral->id ?? NULL;
		}elseif ($referral_code = Module::getRef()){
			$referral          = Referral::findByCode($referral_code);
			$user->referral_id = $referral->id ?? NULL;
		}

		$user->setPassword($this->password);
		$user->generateAuthKey();

		if ($user->save()){
			return $user;
		}

		return NULL;
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
}
