<?php

namespace modules\customer;

use modules\BaseModule;
use modules\customer\console\CronController;
use Yii;
use yii\web\Application;

/**
 * Class Module
 *
 * @package modules\customer
 */
class Module extends BaseModule{

	const COOKIE_REF = 'spider_ref';

	/**
	 * @inheritDoc
	 */
	public function bootstrap($app){
		if ($app instanceof Application && $this->app_id == self::APP_FRONTEND){
			$app->getUrlManager()->addRules([
				['pattern' => "referral/<code:[a-z0-9A-Z]+>", 'route' => "{$this->id}/default/referral"],
				['pattern' => $this->id, 'route' => "{$this->id}/default/index"],
				['pattern' => "{$this->id}/<action:[a-z0-9\-]+>", 'route' => "{$this->id}/default/<action>"],
			], FALSE);
		}

		if ($app instanceof \yii\console\Application){
			$app->controllerMap['player'] = [
				'class' => CronController::class
			];
		}

		parent::bootstrap($app);
	}

	/**
	 * @return void
	 */
	public static function removeRef(){
		$cookies = Yii::$app->response->cookies;
		$cookies->remove(self::COOKIE_REF);
	}

	/**
	 * @return string|null
	 */
	public static function getRef(){
		$cookies = Yii::$app->request->cookies;

		return $cookies->getValue(self::COOKIE_REF);
	}

}