<?php

namespace common\base;

use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;

/**
 * Class AuthManager
 *
 * @package common\base
 */
class AuthManager extends Component{

	/**
	 * @var string
	 */
	protected $cache_prefix = 'GROUP_PERMISSIONS_';

	/**
	 * @return array|mixed
	 */
	protected function getGroupPermissions(){
		if ($groups = Yii::$app->user->identity->roles){

			$cached = TRUE;
			foreach ($groups as $group){
				if (Yii::$app->cache->exists($this->cache_prefix . $group)){
					$cached = FALSE;
					Yii::$app->cache->delete($this->cache_prefix . $group);
				}
			}

			$cache_key = $this->cache_prefix . implode('_', $groups);

			if ($cached && ($permissions = Yii::$app->cache->get($cache_key))){
				return $permissions;
			}

			$permissions = Yii::$app->user->identity->permissions ?? [];
			Yii::$app->cache->set($cache_key, $permissions);

			return $permissions;
		}
	}

	/**
	 * @param $user_id
	 * @param $permission_name
	 * @param array $params
	 *
	 * @return bool
	 */
	public function checkAccess($user_id, $permission_name, $params = []){

		if (strpos('?', $permission_name) !== FALSE){
			return TRUE;
		}

		if (Yii::$app->user->isGuest){
			return FALSE;
		}

		$user = Yii::$app->user->identity;

		if ($user->isAdmin()){
			return TRUE;
		}

		$permissions = $this->getGroupPermissions();

		if (!empty($permissions) && ArrayHelper::isIn($permission_name, $permissions)){
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @param array $keys
	 */
	public function clearCache($keys = []){
		$prefix    = $this->cache_prefix;
		$clear_key = ArrayHelper::map($keys, function ($data) use ($prefix){
			return $prefix . $data;
		}, function (){
			return TRUE;
		});

		Yii::$app->cache->multiSet($clear_key);
	}
}