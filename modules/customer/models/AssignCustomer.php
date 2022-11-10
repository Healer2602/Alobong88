<?php

namespace modules\customer\models;

use Yii;
use yii\base\Model;

/**
 * Class AssignCustomer
 *
 * @package modules\customer\models
 */
class AssignCustomer extends Model{

	public $email;
	public $referral_id;

	/**
	 * @return array
	 */
	public function rules(){
		return [
			['email', 'required'],
			['email', 'email'],
			['email', 'validateEmail'],
		];
	}

	/**
	 * Validate customer email
	 */
	public function validateEmail(){
		$customer = Customer::find()
		                    ->andWhere(['email' => $this->email])
		                    ->andWhere(['referral_id' => NULL])
		                    ->exists();

		if (empty($customer)){
			$this->addError('email',
				Yii::t('customer',
					'Email is not existing or assigned to another referral already.'));
		}
	}

	/**
	 * @return bool
	 */
	public function assign(){
		if ($this->validate()){
			$customer = Customer::find()
			                    ->andWhere(['email' => $this->email])
			                    ->andWhere(['referral_id' => NULL])
			                    ->one();

			if (!empty($customer)){
				$customer->referral_id = $this->referral_id;

				return $customer->save();
			}
		}

		return FALSE;
	}
}