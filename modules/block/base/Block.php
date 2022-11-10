<?php

namespace modules\block\base;

use modules\BaseWidget;


/**
 * Class Widget
 *
 * @package modules\foretail\widget
 *
 * @property-read \modules\block\base\Config $config
 */
abstract class Block extends BaseWidget{

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var string
	 */
	public $block_id;

	/**
	 * @var \modules\block\base\Config
	 */
	public $config_class;

	/**
	 * @var \modules\block\models\Block $data
	 */
	public $data;

	/**
	 * @var string
	 */
	public $title_tag = "h3";

	/**
	 * @var string
	 */
	public $title_url;

	/**
	 * @var string
	 */
	public $full_width;

	/**
	 * @var string
	 */
	public $css_class;

	/**
	 * @var \modules\product\models\Product
	 */
	public $product;

	/**
	 * @return \modules\block\base\Config|NULL
	 */
	public function getConfig(){
		if (!empty($this->config_class) && class_exists($this->config_class)){
			return new $this->config_class;
		}

		return NULL;
	}
}