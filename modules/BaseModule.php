<?php

namespace modules;

use Yii;
use yii\base\BootstrapInterface;
use yii\base\Module;
use yii\helpers\ArrayHelper;
use yii\web\Application;

/**
 * Class BaseModule
 *
 * @package modules
 */
class BaseModule extends Module implements BootstrapInterface{

	const APP_FRONTEND = 'frontend';

	const APP_BACKEND = 'backend';

	public $app_id;

	protected $rule_append = FALSE;

	public $deployments = [];

	/**
	 * {@inheritdoc}
	 */
	public function init(){
		if (Yii::$app->id == 'admin'){
			$this->app_id = self::APP_BACKEND;
		}else{
			$this->app_id = self::APP_FRONTEND;
		}

		$this->controllerNamespace = "modules\\{$this->id}\\{$this->app_id}\\controllers";

		if (empty(Yii::$app->i18n->translations[$this->id])){
			Yii::$app->i18n->translations[$this->id] = [
				'class'            => 'common\base\MessageSource',
				'basePath'         => "@modules/{$this->id}/languages",
				'fileMap'          => [
					$this->id => "{$this->id}.php"
				],
				'forceTranslation' => TRUE
			];
		}

		parent::init();
	}

	/**
	 * @inheritDoc
	 */
	public function bootstrap($app){
		if ($app instanceof Application && $this->app_id == self::APP_BACKEND){
			$app->getUrlManager()->addRules([
				['pattern' => $this->id, 'route' => "{$this->id}/default/index"],
				['pattern' => "{$this->id}/<controller:[a-z0-9\-]+>", 'route' => "{$this->id}/<controller>/index"],
				['pattern' => "{$this->id}/<controller:[a-z0-9\-]+>/<action:[a-z0-9\-]+>/<id:\d+>", 'route' => "{$this->id}/<controller>/<action>"],
				['pattern' => "{$this->id}/<controller:[a-z0-9\-]+>/<action:[a-z0-9\-]+>", 'route' => "{$this->id}/<controller>/<action>"],
			], $this->rule_append);
		}

		if (empty($app->getI18n()->translations[$this->id])){
			$app->getI18n()->translations[$this->id] = [
				'class'            => 'yii\i18n\PhpMessageSource',
				'basePath'         => "@modules/{$this->id}/languages",
				'fileMap'          => [
					$this->id => "{$this->id}.php"
				],
				'forceTranslation' => TRUE
			];
		}

		if (!empty($this->deployments)){
			$app->params['deployments'] = ArrayHelper::merge($app->params['deployments'],
				$this->deployments);
		}
	}

	/**
	 * @return string
	 */
	public function getViewPath(){
		if ($theme = Yii::$app->view->theme){
			$view_path = $theme->getBasePath() . '/' . $this->id;
			if (is_dir($view_path)){
				return $view_path;
			}
		}

		$view_path = parent::getViewPath();

		return dirname($view_path) . '/' . $this->app_id . '/' . basename($view_path);
	}
}