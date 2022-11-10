<?php

namespace modules\customer\frontend\models;

use modules\customer\models\Customer;
use Yii;
use yii\base\Model;

/**
 * ChangePasswordForm form
 */
class ChangePasswordForm extends Model{

	public $password;
	public $current_password;
	public $confirm_password;

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['password', 'confirm_password', 'current_password'], 'string'],
			[['password', 'confirm_password', 'current_password'], 'required'],
			['password', 'compare', 'compareAttribute' => 'confirm_password'],
			[['password', 'confirm_password'], 'string', 'min' => 8],
			['confirm_password', 'compare', 'compareAttribute' => 'password'],
			['password', 'compare', 'compareAttribute' => 'confirm_password'],
			['current_password', 'validatePassword'],
		];
	}

	/**
	 * @return array
	 */
	public function attributeLabels(){
		return [
			'password'         => Yii::t('customer', 'Password'),
			'current_password' => Yii::t('customer', 'Current Password'),
			'confirm_password' => Yii::t('customer', 'Confirm Password'),
		];
	}

	/**
	 * @param $attribute
	 *
	 * @return void
	 */
	public function validatePassword($attribute){
		if (!empty($attribute)){
			/**@var Customer $user */
			$user     = Yii::$app->user->identity;
			$hash     = $user->password_hash;
			$validate = Yii::$app->security->validatePassword($this->current_password, $hash);

			if (!$validate){
				$this->addError($attribute, 'Current password is incorrect');
			}
		}
	}

	/**
	 * @return bool
	 * @throws \yii\base\Exception
	 */
	public function change(){
		if (!$this->validate()){
			return NULL;
		}

		/**@var \modules\customer\frontend\models\CustomerIdentity $user */
		$user = Yii::$app->user->identity;
		$user->detachBehavior('audit');

		if (empty($this->password)){
			$user->password_hash = $user->getOldAttribute('password_hash');
		}else{
			$user->password         = $this->password;
			$user->confirm_password = $this->password;
			$user->setPassword($this->password);
			$user->generateAuthKey();
		}

		return $user->save();
	}
}
