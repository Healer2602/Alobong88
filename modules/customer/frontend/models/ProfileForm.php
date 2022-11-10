<?php

namespace modules\customer\frontend\models;

use Yii;
use yii\base\Model;

/**
 * Profile form
 */
class ProfileForm extends Model{

	public $name;
	public $phone;
	public $password;
	public $confirm_password;
	public $username;
	public $email;


	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['name', 'phone'], 'string'],
			[['name', 'phone'], 'required'],
			[['password', 'confirm_password'], 'string', 'min' => 8],
			['confirm_password', 'compare', 'compareAttribute' => 'password']
		];
	}

	/**
	 * @return array
	 */
	public function attributeLabels(){
		return [
			'name'             => Yii::t('customer', 'Name'),
			'phone'            => Yii::t('customer', 'Phone Number'),
			'password'         => Yii::t('customer', 'Password'),
			'confirm_password' => Yii::t('customer', 'Confirm Password'),
			'username'         => Yii::t('customer', 'Username'),
			'email'            => Yii::t('customer', 'Email'),
		];
	}

	/**
	 * Update user profile.
	 *
	 * @return CustomerIdentity|null the saved model or null if saving fails
	 * @throws \yii\base\Exception
	 */
	public function update(){
		if (!$this->validate()){
			return NULL;
		}

		/**@var \modules\customer\frontend\models\CustomerIdentity $user */
		$user               = Yii::$app->user->identity;
		$user->name         = $this->name;
		$user->phone_number = $this->phone;

		if (empty($this->password)){
			$user->setPassword($this->password);
			$user->generateAuthKey();
		}

		return $user->save() ? $user : NULL;
	}
}
