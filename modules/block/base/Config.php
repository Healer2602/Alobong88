<?php

namespace modules\block\base;

use ReflectionClass;
use Yii;
use yii\base\InvalidCallException;

/**
 * Class Config
 *
 * @package modules\foretail\widget
 */
abstract class Config{

	/**
	 * @var \modules\block\models\Block
	 */
	public $model;

	/**
	 * @return string of the config form
	 */
	public abstract function form()
	: string;

	/**
	 * @param string $view
	 * @param array $params
	 *
	 * @return string of the config form
	 * @throws \ReflectionException
	 */
	public function render($view, array $params = []){
		$view_path = $this->_findViewPath($view);
		if (!file_exists($view_path)){
			throw new InvalidCallException("Unable to locate view file for view '$view'.");
		}

		$params['model'] = $this->model;

		return Yii::$app->getView()->renderFile($view_path, $params);
	}

	/**
	 * @param $view
	 * @param array $params
	 *
	 * @return string
	 * @throws \ReflectionException
	 */
	private function _findViewPath($view, array $params = []){
		$class     = new ReflectionClass($this);
		$view_path = dirname($class->getFileName()) . "/views/{$view}.php";

		if ($theme = Yii::$app->view->theme){
			$theme_path = $theme->getBasePath();
			$filename   = str_replace(['modules', "\\"], ['', '/'],
					$class->getNamespaceName()) . '/' . $view;

			$view_theme_path = $theme_path . $filename . '.php';
			if (file_exists($view_theme_path)){
				$view_path = $view_theme_path;
			}
		}

		return $view_path;
	}
}