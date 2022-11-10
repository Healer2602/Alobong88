<?php

namespace modules\block\backend\models;

use modules\block\models\Block;

/**
 * Class BlockModel
 *
 * @package modules\block\backend\models
 */
class BlockModel extends Block{

	/**
	 * @var \modules\block\base\Config
	 */
	private $_config;

	/**
	 * @inheritDoc
	 */
	public function init(){
		parent::init();

		if ($this->_config === NULL){
			$this->_config = $this->_getConfigs();
		}
	}

	/**
	 * @inheritDoc
	 */
	public function afterFind(){
		parent::afterFind();

		if ($this->_config === NULL){
			$this->_config = $this->_getConfigs();
		}
	}

	/**
	 * @return \modules\block\base\Config|null
	 */
	private function _getConfigs(){
		$config = NULL;

		if ($object = $this->formObject()){
			if ($config = $object->config){
				$config->model = $this;
			}
		}

		return $config;
	}

	/**
	 * @return string
	 */
	public function renderForm(){
		if (empty($this->_config)){
			return NULL;
		}

		return $this->_config->form();
	}
}