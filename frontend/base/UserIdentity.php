<?php

namespace frontend\base;

use yii\web\IdentityInterface;

/**
 * Class UserIdentity
 *
 * @package frontend\base
 */
class UserIdentity implements IdentityInterface{

	/**
	 * @inheritDoc
	 */
	public static function findIdentity($id){
		return NULL;
	}

	/**
	 * @inheritDoc
	 */
	public static function findIdentityByAccessToken($token, $type = NULL){
		return NULL;
	}

	/**
	 * @inheritDoc
	 */
	public function getId(){
		return NULL;
	}

	/**
	 * @inheritDoc
	 */
	public function getAuthKey(){
		return NULL;
	}

	/**
	 * @inheritDoc
	 */
	public function validateAuthKey($authKey){
		return NULL;
	}
}