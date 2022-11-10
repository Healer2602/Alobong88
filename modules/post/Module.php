<?php

namespace modules\post;

use modules\BaseModule;
use yii\web\Application;

/**
 * post module definition class
 */
class Module extends BaseModule{

	/**
	 * @inheritDoc
	 */
	public function bootstrap($app){
		parent::bootstrap($app);

		if ($app instanceof Application){
			if ($this->app_id == self::APP_BACKEND){
				$rules = [
					[
						'pattern'  => 'promotion/<slug:[a-z0-9\-]+>.html',
						'route'    => 'post/post/index',
						'defaults' => ['type' => 'post'],
					],
					[
						'pattern'  => 'information/<slug:[a-z0-9\-]+>.html',
						'route'    => 'post/information/index',
						'defaults' => ['type' => 'information'],
					],
				];
			}elseif ($this->app_id == self::APP_FRONTEND){
				$rules = [
					[
						'pattern'  => 'promotion/<slug:[a-z0-9\-]+>.html',
						'route'    => 'post/post/index',
						'defaults' => ['type' => 'post'],
					],
					[
						'pattern'  => 'promotion',
						'route'    => 'post/post/list',
						'defaults' => ['type' => 'post'],
					],
					[
						'pattern' => 'promotion/<slug:[a-z0-9\-]+>',
						'route'   => 'post/category/index',
					],
					[
						'pattern'  => 'information/<slug:[a-z0-9\-]+>.html',
						'route'    => 'post/information/index',
						'defaults' => ['type' => 'information'],
					],
					[
						'pattern'  => 'information',
						'route'    => 'post/information/list',
						'defaults' => ['type' => 'information'],
					],
				];
			}

			if (!empty($rules)){
				$app->getUrlManager()->addRules($rules, FALSE);
			}
		}
	}
}