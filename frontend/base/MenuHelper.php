<?php

namespace frontend\base;

use Yii;
use yii\base\BaseObject;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * Class MenuHelper
 *
 * @package frontend\base
 */
final class MenuHelper extends BaseObject{

	/**
	 * @param $menu
	 * @param int $left
	 * @param null $right
	 * @param array $item_options
	 *
	 * @return array
	 * @throws \yii\base\InvalidConfigException
	 */
	public static function createTree($menu, $left = 1, $right = NULL, $item_options = []){
		$current_url = Yii::$app->request->getUrl();
		$tree        = [];
		foreach ($menu as $range){
			if ($range['lft'] == $left + 1 && (is_null($right) || $range['rgt'] < $right)){
				$range['children'] = self::createTree($menu, $range['lft'], $range['rgt']);

				if (!empty($range['status'])){
					$label = $range['name'];
					if (!empty($range['icon'])){
						$label = Html::img($range['icon']) . Html::tag('span', $label);
					}

					if (strpos($range['menu_path'], 'http') !== FALSE){
						$url = $range['menu_path'];
					}else{
						$url = '/' . ltrim($range['menu_path'], '/');
					}

					$tree_leaf = [
						'id'    => $range['id'],
						'label' => $label,
						'url'   => $url,
						'items' => $range['children']
					];

					if (is_string($url) && $url == $current_url){
						$tree_leaf['active'] = TRUE;
					}

					if (!empty($range['params']) && is_string($range['params'])){
						$tree_leaf['params'] = Json::decode($range['params']);
					}

					if (!empty($range['children'])){
						$tree_leaf['options'] = $item_options;
					}

					$tree[] = $tree_leaf;
				}

				$left = $range['rgt'];
			}
		}

		return $tree;
	}
}