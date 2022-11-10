<?php

namespace frontend\base;

use modules\website\models\WebsiteSetting;

/**
 * Trait Setting
 *
 * @package frontend\base
 *
 * @property-read WebsiteSetting $setting
 */
trait Setting{

	/**
	 * @var null
	 */
	private $_setting = NULL;

	/**
	 * @return WebsiteSetting|null
	 */
	public function getSetting(){
		if ($this->_setting === NULL){
			$settings = new WebsiteSetting;
			$settings->getValues();
			$this->_setting = $settings;
		}

		return $this->_setting;
	}
}