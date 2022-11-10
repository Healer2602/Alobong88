<?php

namespace modules\customer\frontend\models;

use modules\customer\models\Customer;
use Yii;
use yii\base\NotSupportedException;
use yii\web\IdentityInterface;

/**
 * Client User model
 *
 * @inheritDoc
 */
class CustomerIdentity extends Customer implements IdentityInterface{

	const STATUS_DELETED = 0;

	const STATUS_ACTIVE = 10;

	/**
	 * {@inheritdoc}
	 */
	public static function findIdentity($id){
		return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
	}

	/**
	 * {@inheritdoc}
	 * @throws \yii\base\NotSupportedException
	 */
	public static function findIdentityByAccessToken($token, $type = NULL){
		throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
	}

	/**
	 * Finds user by username
	 *
	 * @param string $username
	 *
	 * @return static|null
	 */
	public static function findByUsername($username){
		return static::find()
		             ->andWhere(['status' => self::STATUS_ACTIVE])
		             ->andWhere(['OR', ['username' => $username], ['email' => $username]])
		             ->one();
	}

	/**
	 * Finds user by password reset token
	 *
	 * @param string $token password reset token
	 *
	 * @return static|null
	 */
	public static function findByPasswordResetToken($token){
		if (!static::isPasswordResetTokenValid($token)){
			return NULL;
		}

		return static::findOne([
			'password_reset_token' => $token,
			'status'               => self::STATUS_ACTIVE,
		]);
	}

	/**
	 * Finds out if password reset token is valid
	 *
	 * @param string $token password reset token
	 *
	 * @return bool
	 */
	public static function isPasswordResetTokenValid($token){
		if (empty($token)){
			return FALSE;
		}

		$timestamp = (int) substr($token, strrpos($token, '_') + 1);
		$expire    = Yii::$app->params['user.passwordResetTokenExpire'];

		return $timestamp + $expire >= time();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getId(){
		return $this->getPrimaryKey();
	}

	/**
	 * {@inheritdoc}
	 */
	public function validateAuthKey($authKey){
		return $this->getAuthKey() === $authKey;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAuthKey(){
		return $this->auth_key;
	}

	/**
	 * Validates password
	 *
	 * @param string $password password to validate
	 *
	 * @return bool if password provided is valid for current user
	 */
	public function validatePassword($password){
		return Yii::$app->security->validatePassword($password, $this->password_hash);
	}

	/**
	 * Generates password hash from password and sets it to the model
	 *
	 * @param string $password
	 */
	public function setPassword($password){
		$this->password_hash    = $password;
		$this->password         = $password;
		$this->confirm_password = $password;
	}

	/**
	 * Generates "remember me" authentication key
	 *
	 * @throws \yii\base\Exception
	 */
	public function generateAuthKey(){
		$this->auth_key = Yii::$app->security->generateRandomString();
	}

	/**
	 * Generates new password reset token
	 *
	 * @throws \yii\base\Exception
	 */
	public function generatePasswordResetToken(){
		$this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
	}

	/**
	 * Removes password reset token
	 */
	public function removePasswordResetToken(){
		$this->password_reset_token = NULL;
	}

	/**
	 * @return \modules\customer\frontend\models\CustomerIdentity|null
	 */
	public static function profile(){
		if (Yii::$app->user->isGuest){
			return NULL;
		}

		return Yii::$app->user->identity;
	}
}
