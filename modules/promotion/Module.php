<?php

namespace modules\promotion;

use modules\BaseModule;
use modules\promotion\console\CronController;
use yii\console\Application;

/**
 * Class Module
 *
 * @package modules\promotion
 */
class Module extends BaseModule{

	/**
	 * @inheritDoc
	 *
	 * @param $app
	 */
	public function bootstrap($app){
		if ($app instanceof Application){
			$app->controllerMap['promo'] = [
				'class' => CronController::class
			];
		}

		parent::bootstrap($app);
	}
}