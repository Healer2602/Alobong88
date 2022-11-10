<?php

namespace modules\block\blocks\config;

use modules\block\base\Config;

/**
 * Class Html
 *
 * @package modules\block\blocks\config
 */
class CustomHtml extends Config{

	/**
	 * @return string of the config form
	 * @throws \ReflectionException
	 */
	public function form()
	: string{
		return $this->render('custom-html');
	}
}