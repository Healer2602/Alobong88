<?php

namespace modules;

use Yii;
use yii\base\Widget;

/**
 * Class BaseWidget
 *
 * @package modules
 */
class BaseWidget extends Widget{

	/**
	 * @param string $view
	 * @param array $params
	 *
	 * @return string
	 */
	public function render($view, $params = []){
		if ($theme = $this->getView()->theme){
			$theme_path = $theme->getBasePath();
			$filename   = dirname(str_replace(['modules', "\\", "/"],
					['', DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR],
					get_class($this))) . DIRECTORY_SEPARATOR . $view;

			$view_theme_path = $theme_path . $filename . '.php';
			if (file_exists($view_theme_path)){
				$root            = str_replace(["\\", "/"], DIRECTORY_SEPARATOR,
					dirname(Yii::getAlias('@modules')));
				$view_theme_path = str_replace(["\\", "/"], DIRECTORY_SEPARATOR, $view_theme_path);

				$view = str_replace($root, '', $view_theme_path);
				$view = '@' . ltrim($view, DIRECTORY_SEPARATOR);
				$view = str_replace(["\\", "/"], "/", $view);
			}
		}

		return parent::render($view, $params);
	}

}