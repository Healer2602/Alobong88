<?php

namespace common\base;

use Yii;
use yii\base\BaseObject;

/**
 * Class Encryption
 *
 * @package common\base
 */
class Encryption extends BaseObject{

	/**
	 * @return false|string
	 */
	private static function _findKey(){
		$key_path = Yii::$app->params['file.key'] ?? NULL;
		if (!empty($key_path) && file_exists($key_path)){
			$key = @file_get_contents($key_path);
		}

		if (empty($key)){
			$key = 'WB4oKZ8PxFZSn7Px4PZRXP1YmgLnLthXXPx4PZRXP1';
		}

		return $key;
	}

	/**
	 * @param $data
	 *
	 * @return string
	 */
	public static function encrypt($data){
		$encrypted = Yii::$app->security->encryptByKey($data, self::_findKey());

		return base64_encode($encrypted);
	}

	/**
	 * @param $data
	 *
	 * @return bool|string
	 */
	public static function decrypt($data){
		$encrypted = base64_decode($data);

		return Yii::$app->security->decryptByKey($encrypted, self::_findKey());
	}

}