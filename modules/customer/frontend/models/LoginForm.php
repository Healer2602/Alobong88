<?php

namespace modules\customer\frontend\models;

use modules\spider\recaptcha\Validator;
use Yii;
use yii\base\Model;

/**
 * Login form
 *
 * @property-read \modules\customer\frontend\models\CustomerIdentity|null $user
 */
class LoginForm extends Model{

	public $username;
	public $password;
	public $rememberMe = TRUE;
	public $captcha;

	private $_user;


	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['username', 'password'], 'required'],
			[['rememberMe'], 'boolean'],
			['password', 'validatePassword'],
			[['captcha'], Validator::class],
		];
	}

	/**
	 * @return array
	 */
	public function attributeLabels(){
		return [
			'username'   => Yii::t('customer', 'Username or email'),
			'password'   => Yii::t('customer', 'Password'),
			'rememberMe' => Yii::t('customer', 'Remember me'),
		];
	}

	/**
	 * Validates the password.
	 * This method serves as the inline validation for password.
	 *
	 * @param string $attribute the attribute currently being validated
	 * @param array $params the additional name-value pairs given in the rule
	 */
	public function validatePassword($attribute, $params){
		if (!$this->hasErrors()){
			$user = $this->getUser();
			if (!$user || !$user->validatePassword($this->password)){
				$this->addError($attribute, Yii::t('customer', 'Incorrect username or password.'));
			}
		}
	}

	/**
	 * Logs in a user using the provided username and password.
	 *
	 * @return bool whether the user is logged in successfully
	 */
	public function login(){
		if ($this->validate()){
			return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
		}

		return FALSE;
	}

	/**
	 * Finds user by [[username]]
	 *
	 * @return CustomerIdentity|null
	 */
	protected function getUser(){
		if ($this->_user === NULL){
			$this->_user = CustomerIdentity::findByUsername($this->username);
		}

		return $this->_user;
	}
}
