<?php

namespace backend\models;

use Symfony\Component\Yaml\Yaml;
use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;

/**
 * Class Setting
 *
 * @package backend\models
 */
class Setting extends Component{

	/**
	 * @return array|mixed
	 */
	public function list(){
		$app_path = Yii::getAlias("@backend/setting.yml");
		$setting  = [];

		if (file_exists($app_path)){
			$setting = Yaml::parse(file_get_contents($app_path));
		}

		$bootstraps = Yii::$app->bootstrap;

		foreach ($bootstraps as $bootstrap){
			$notification_file = Yii::getAlias("@modules/{$bootstrap}/setting.yml");
			if (file_exists($notification_file)){
				$app_setting = Yaml::parse(file_get_contents($notification_file));
				$setting     = array_merge($setting, $app_setting);
			}
		}

		ArrayHelper::multisort($setting, 'weight');

		return $setting;
	}

}