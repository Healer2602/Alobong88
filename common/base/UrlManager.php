<?php

namespace common\base;

use yii\web\UrlManager as BaseUrlManager;

/**
 * Class UrlManager
 *
 * @package common\base
 */
class UrlManager extends BaseUrlManager{

	/**
	 * @param array|string $params
	 *
	 * @return string
	 * @throws \Throwable
	 */
	public function createUrl($params){
		return parent::createUrl($params);
	}

}