<?php

namespace backend\models;

use Yii;
use yii\base\InvalidArgumentException;
use yii\base\Model;

/**
 * Password reset form
 */
class ResetPasswordForm extends Model{

	public $password;

	public $confirm_password;

	/**
	 * @var \backend\models\Staff
	 */
	private $_user;


	/**
	 * Creates a form model given a token.
	 *
	 * @param string $token
	 * @param array $config name-value pairs that will be used to initialize the object properties
	 *
	 * @throws \yii\base\InvalidArgumentException if token is empty or not valid
	 */
	public function __construct($token, $config = []){
		if (empty($token) || !is_string($token)){
			throw new InvalidArgumentException('Password reset token cannot be blank.');
		}

		$this->_user = Staff::findByPasswordResetToken($token);
		if (!$this->_user){
			throw new InvalidArgumentException('Wrong password reset token.');
		}

		parent::__construct($config);
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['password', 'confirm_password'], 'required'],
			['password', 'string', 'min' => 6],
			['confirm_password', 'compare', 'compareAttribute' => 'password'],
		];
	}

	/**
	 * @return array
	 */
	public function attributeLabels(){
		return [
			'password'         => Yii::t('app', 'Password'),
			'confirm_password' => Yii::t('app', 'Confirm password'),
		];
	}

	/**
	 * Resets password.
	 *
	 * @return bool if password was reset.
	 * @throws \yii\base\Exception
	 */
	public function resetPassword(){
		$user = $this->_user;
		$user->setPassword($this->password);
		$user->removePasswordResetToken();

		return $user->save(FALSE);
	}
}
