<?php

namespace modules\agent;

use modules\agent\api\controllers\ApiController;
use modules\BaseModule;
use Yii;
use yii\web\Application;

/**
 * Class Module
 *
 * @package modules\agent
 */
class Module extends BaseModule{

	const COOKIE_REF = 'spider_agent';

	/**
	 * @param \yii\base\Application $app
	 */
	public function bootstrap($app){
		parent::bootstrap($app);

		if ($app instanceof Application && $this->app_id == self::APP_FRONTEND){
			$app->getUrlManager()->addRules([
				['pattern' => "ref/<code:[a-zA-Z0-9]+>", 'route' => "{$this->id}/default/index"],
				['pattern' => "api/agent", 'route' => "{$this->id}/api/index"],
				['pattern' => "api/agent/<action:[a-z0-9\-]+>", 'route' => "{$this->id}/api/<action>"],
			]);
		}
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