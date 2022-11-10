<?php

namespace backend\models;

use Yii;
use yii\base\Model;

/**
 * Login form
 */
class LoginForm extends Model{

	public $username;
	public $password;
	public $rememberMe = TRUE;

	private $_user;


	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			// username and password are both required
			[['username', 'password'], 'required'],
			// rememberMe must be a boolean value
			['rememberMe', 'boolean'],
			// password is validated by validatePassword()
			['password', 'validatePassword'],
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
				$this->addError($attribute, 'Incorrect username or password.');
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
	 * @return Staff|null
	 */
	protected function getUser(){
		if ($this->_user === NULL){
			$this->_user = Staff::findByUsername($this->username);
		}

		return $this->_user;
	}
}
