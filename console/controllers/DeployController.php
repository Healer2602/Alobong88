<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\ArrayHelper;

/**
 * Class DeployController
 *
 * @package console\controllers
 */
class DeployController extends Controller{

	public $mod;
	public $interactive = FALSE;

	/**
	 * @param string $actionID
	 *
	 * @return string[]
	 */
	public function options($actionID){
		return ArrayHelper::merge(parent::options($actionID), ['mod']);
	}

	/**
	 * @return string[]
	 */
	public function optionAliases(){
		return [
			'm' => 'mod'
		];
	}

	/**
	 * @var string
	 */
	public $defaultAction = 'up';

	/**
	 * @throws \yii\base\InvalidRouteException
	 * @throws \yii\console\Exception
	 */
	public function actionUp(){
		Yii::$app->runAction('migrate',
			['migrationPath' => '@console/migrations', 'interactive' => $this->interactive]);

		if ($this->mod){
			$bootstraps = [$this->mod];
		}else{
			$bootstraps = Yii::$app->bootstrap;
		}

		foreach ($bootstraps as $bootstrap){
			$migration_path = Yii::getAlias("@modules/{$bootstrap}/migrations");
			if (is_dir($migration_path)){
				echo "Run migration for module {$bootstrap}:\n";

				Yii::$app->runAction('migrate',
					['interactive' => $this->interactive, 'migrationNamespaces' => "modules\\{$bootstrap}\\migrations"]);
			}
		}

		if ($deployments = Yii::$app->params['deployments']){
			foreach ($deployments as $item){
				Yii::$app->runAction($item);
			}
		}

		Yii::$app->runAction('permissions');
		Yii::$app->runAction('cache/flush-all');
	}

	/**
	 * @return int
	 * @throws \yii\base\Exception
	 */
	public function actionGenerate(){
		echo Yii::$app->security->generatePasswordHash($this->mod);

		return ExitCode::OK;
	}
}