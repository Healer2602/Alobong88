<?php

namespace console\controllers;

use backend\models\UserGroupPermission;
use backend\models\UserPermission;
use Symfony\Component\Yaml\Yaml;
use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

/**
 * Class PermissionsController
 *
 * @package console\controllers
 */
class PermissionsController extends Controller{

	public function actionIndex(){
		$permissions = $this->_findPermissions();

		$permission_data = [];

		foreach ($permissions as $permission_name => $item){
			if (!$user_permission = UserPermission::findOne(['name' => $permission_name])){
				$user_permission = new UserPermission([
					'name' => $permission_name
				]);
			}

			$user_permission->description = $item['name'] ?? $permission_name;
			$user_permission->synced      = 1;
			if ($user_permission->save()){
				$permission_data[] = $user_permission;
			}

			if (!empty($item['children'])){
				foreach ($item['children'] as $child_permission_name => $child){
					$child_permission_name = implode(" ",
						array_unique([$permission_name, $child_permission_name]));

					if (!$user_permission = UserPermission::findOne(['name' => $child_permission_name])){
						$user_permission = new UserPermission([
							'name' => $child_permission_name
						]);
					}

					$user_permission->description = $child['name'] ?? $child_permission_name;
					$user_permission->synced      = 1;
					if ($user_permission->save()){
						$permission_data[] = $user_permission;
					}
				}
			}
		}

		if ($permission_data){
			$old_permissions = UserPermission::find()
			                                 ->andWhere(['synced' => 0])
			                                 ->indexBy('id')
			                                 ->asArray()
			                                 ->all();

			if (!empty($old_permissions)){
				$old_permissions = array_keys($old_permissions);
				UserGroupPermission::deleteAll(['user_permission_id' => $old_permissions]);
				UserPermission::deleteAll(['id' => $old_permissions]);
			}

			UserPermission::updateAll(['synced' => 0]);

			echo Yii::t('common',
					"Total {n,plural,=1{1 permission is} other{# permissions are}} updated",
					['n' => count($permission_data)]) . "\n";
		}else{
			echo "No any new permissions are found\n";
		}
	}

	/**
	 * @return array|mixed
	 */
	private function _findPermissions(){
		$permissions = [];
		$app_path    = Yii::getAlias("@backend/permissions.yml");
		if (file_exists($app_path)){
			$permissions = Yaml::parse(file_get_contents($app_path));
		}

		$bootstraps = Yii::$app->bootstrap;

		foreach ($bootstraps as $bootstrap){
			$permission_file = Yii::getAlias("@modules/{$bootstrap}/permissions.yml");
			if (file_exists($permission_file)){
				$app_permissions = Yaml::parse(file_get_contents($permission_file));
				$permissions     = ArrayHelper::merge($permissions, $app_permissions);
			}
		}

		return $permissions;
	}
}