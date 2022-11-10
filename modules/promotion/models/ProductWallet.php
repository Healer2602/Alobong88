<?php

namespace modules\promotion\models;

use modules\game\models\ProductWallet as BaseProductWallet;

/**
 * This is the model class ProductWallet
 */
class ProductWallet extends BaseProductWallet{

	/**
	 * @return array
	 * @throws \Throwable
	 */
	public static function list(){
		return self::findList('',[],'code','name');
	}
}