<?php

namespace modules\block\blocks;

use modules\block\base\Block;

/**
 * Class Html
 *
 * @package modules\page\blocks
 */
class CustomHtml extends Block{

	/**
	 * @var string
	 */
	public $config_class = config\CustomHtml::class;

	/**
	 * @inheritDoc
	 */
	public function run(){
		return $this->render('custom-html', [
			'data'       => $this->data,
			'config'     => $this->data,
			'title_tag'  => $this->title_tag,
			'full_width' => $this->full_width,
			'css_class'  => $this->css_class,
			'block_id'   => $this->block_id
		]);
	}
}