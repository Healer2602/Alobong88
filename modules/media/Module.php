<?php

namespace modules\media;

use modules\BaseModule;
use yii\web\Application;

/**
 * Media module definition class
 */
class Module extends BaseModule{

	public $permission = 'media';

	/**
	 * @param $app
	 */
	public function bootstrap($app){
		parent::bootstrap($app);

		if ($app instanceof Application){
			$app->getUrlManager()->addRules([
				['class' => 'yii\web\UrlRule', 'pattern' => $this->id, 'route' => "{$this->id}/manager/index"],
				['class' => 'yii\web\UrlRule', 'pattern' => "{$this->id}/<action:[a-z0-9\-]+>", 'route' => "{$this->id}/manager/<action>"],
			], FALSE);
		}
	}
}
