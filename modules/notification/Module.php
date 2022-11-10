<?php

namespace modules\notification;

use modules\BaseModule;
use modules\notification\console\TriggerController;
use yii\console\Application;

/**
 * Class Module
 *
 * @package modules\notification
 */
class Module extends BaseModule{

	/**
	 * @param \yii\base\Application $app
	 */
	public function bootstrap($app){
		if ($app instanceof Application){
			$app->controllerMap['trigger'] = [
				'class' => TriggerController::class
			];
		}

		$this->deployments['trigger'] = 'trigger';

		parent::bootstrap($app);
	}
}