<?php

namespace modules\block\blocks;

use modules\block\base\Block;

/**
 * Class Html
 *
 * @package modules\block\blocks
 */
class Html extends Block{

	/**
	 * @var string
	 */
	public $config_class = config\Html::class;

	/**
	 * @inheritDoc
	 */
	public function run(){
		return $this->render('html', [
			'data'       => $this->data,
			'config'     => $this->data,
			'title_tag'  => $this->title_tag,
			'full_width' => $this->full_width,
			'block_id'   => $this->block_id
		]);
	}
}