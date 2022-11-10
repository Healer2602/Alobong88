<?php

namespace modules\block;

use modules\block\models\Block;

/**
 * Class BlockWidget
 *
 * @package modules\block
 */
class BlockWidget extends Block{

	/**
	 * @param array $options
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function display($options = []){
		$title_tag = $options['title_tag'] ?? 'h3';

		/**@var \yii\base\Widget $class */
		if ($class = $this->formObject()){
			return $class::widget([
				'data'      => $this->toArray(),
				'title_tag' => $title_tag,
				'block_id'  => 'block',
				'css_class' => $this->setting['css_class'] ?? ''
			]);
		}

		return NULL;
	}
}