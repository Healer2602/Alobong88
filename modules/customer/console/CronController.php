<?php

namespace modules\customer\console;

use modules\customer\models\Session;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * Class CronController
 */
class CronController extends Controller{

	/**
	 * @return int
	 */
	public function actionIndex(){
		Session::deleteAll(['<', 'expire', time() - 30 * 86400]);

		return ExitCode::OK;
	}
}
