<?php

namespace modules\wallet;

use modules\BaseModule;
use modules\wallet\console\CronController;
use yii\web\Application;

/**
 * Class Module
 *
 * @package modules\wallet
 */
class Module extends BaseModule{

	/**
	 * @inheritDoc
	 */
	public function bootstrap($app){
		if ($app instanceof Application && $this->app_id == self::APP_FRONTEND){
			$app->getUrlManager()->addRules([
				['pattern' => "{$this->id}/transfer", 'route' => "{$this->id}/transfer/index"],
				['pattern' => "{$this->id}/history/bet", 'route' => "{$this->id}/history/index"],
				['pattern' => "{$this->id}/transfer/<action:[a-z0-9\-]+>", 'route' => "{$this->id}/transfer/<action>"],

				['pattern' => "{$this->id}/topup/callback", 'route' => "{$this->id}/default/topup-callback"],
				['pattern' => "{$this->id}/withdraw/callback", 'route' => "{$this->id}/default/withdraw-callback"],

				['pattern' => "{$this->id}/return/<type:[a-z0-9\-]+>/<id:[0-9]+>", 'route' => "{$this->id}/default/topup-return"],
				['pattern' => "{$this->id}/withdraw/<type:[a-z0-9\-]+>/<id:[0-9]+>", 'route' => "{$this->id}/default/withdraw-return"],
				['pattern' => "{$this->id}/<action:[a-z0-9\-]+>", 'route' => "{$this->id}/default/<action>"],
			], FALSE);
		}

		if ($app instanceof \yii\console\Application){
			$app->controllerMap['wallet'] = [
				'class' => CronController::class
			];
		}

		parent::bootstrap($app);
	}
}