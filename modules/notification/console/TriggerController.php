<?php

namespace modules\notification\console;

use modules\notification\models\Trigger;
use Symfony\Component\Yaml\Yaml;
use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

/**
 * Class TriggerController
 *
 * @package console\controllers
 */
class TriggerController extends Controller{

	/**
	 * @inheritDoc
	 */
	public function actionIndex(){
		$triggers = $this->_findTriggers();

		$total = 0;

		foreach ($triggers as $key => $trigger){
			if (!$template = Trigger::findOne(['key' => $key])){
				$template = new Trigger([
					'key' => $key
				]);
			}

			$template->name   = $trigger['name'];
			$template->level  = $trigger['level'] ?? NULL;
			$template->params = $trigger['params'] ?? [];

			$template->save(FALSE);
			$total ++;
		}

		Trigger::deleteAll(['NOT', ['key' => array_keys($triggers)]]);

		echo "Total {$total} triggers are synced\n";
	}

	/**
	 * @return array|mixed
	 */
	private function _findTriggers(){
		$app_path      = Yii::getAlias("@backend/notification.yml");
		$notifications = [];

		if (file_exists($app_path)){
			$notifications = Yaml::parse(file_get_contents($app_path));
		}

		$bootstraps = Yii::$app->bootstrap;

		foreach ($bootstraps as $bootstrap){
			$notification_file = Yii::getAlias("@modules/{$bootstrap}/notification.yml");
			if (file_exists($notification_file)){
				$app_notifications = Yaml::parse(file_get_contents($notification_file));
				$notifications     = ArrayHelper::merge($notifications, $app_notifications);
			}
		}

		return $notifications;
	}
}