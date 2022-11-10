<?php

namespace modules\game;

use modules\BaseModule;
use modules\game\console\CronController;
use yii\web\Application;

/**
 * Class Module
 *
 * @package modules\game
 */
class Module extends BaseModule{

	/**
	 * @inheritDoc
	 *
	 * @param $app
	 */
	public function bootstrap($app){
		if ($app instanceof Application && $this->app_id == self::APP_FRONTEND){
			$app->getUrlManager()->addRules([
				['pattern' => "{$this->id}/partner/<slug:[a-z0-9\-]+>", 'route' => "{$this->id}/vendor/view"],
				['pattern' => "{$this->id}/partners", 'route' => "{$this->id}/vendor/index"],

				['pattern' => "{$this->id}/play/<id:[0-9\-]+>", 'route' => "{$this->id}/default/index"],
				['pattern' => "{$this->id}/try/<id:[0-9\-]+>", 'route' => "{$this->id}/default/try"],
				['pattern' => "{$this->id}/<slug:[a-z0-9\-]+>/<partner:[a-z0-9\-]+>", 'route' => "{$this->id}/type/index"],
				['pattern' => "{$this->id}/<slug:[a-z0-9\-]+>", 'route' => "{$this->id}/type/index"],

			], FALSE);
		}

		if ($app instanceof \yii\console\Application){
			$app->controllerMap['game'] = [
				'class' => CronController::class
			];
		}

		parent::bootstrap($app);
	}
}