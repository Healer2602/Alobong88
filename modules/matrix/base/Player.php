<?php

namespace modules\matrix\base;

use modules\customer\models\Customer;
use yii\base\BaseObject;

/**
 * Player Account
 */
class Player extends BaseObject{


	/**
	 * @param \modules\customer\models\Customer $model
	 *
	 * @return false|int
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\httpclient\Exception
	 */
	public static function createAccount(Customer $model){
		$player = [
			"PlayerId" => $model->username,
			"Currency" => $model->currency,
			"Password" => $model->playPass,
		];

		$data = API::post('/Player/Register', $player);
		if (!empty($data)){
			return Customer::updateAll(['has_account' => TRUE], ['id' => $model->id]);
		}

		return FALSE;
	}
}