<?php

namespace modules\copay;

use modules\BaseModule;
use modules\copay\console\CronController;
use yii\console\Application;

/**
 * Class Module
 *
 * @package modules\copay
 */
class Module extends BaseModule{

	/**
	 * @inheritDoc
	 *
	 * @param $app
	 */
	public function bootstrap($app){

		if ($app instanceof Application){
			$app->controllerMap['copay'] = [
				'class' => CronController::class
			];
		}

		parent::bootstrap($app);
	}
}