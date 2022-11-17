<?php

namespace modules\website\widgets;

use frontend\base\MenuHelper;
use modules\website\models\Menu as MenuModel;
use yii\base\InvalidConfigException;
use yii\bootstrap5\Nav;

/**
 * Class NavMenu
 *
 * @package modules\website\widgets
 */
class NavMenu extends Nav{

	public $position;

	public $encodeLabels = FALSE;

	public $itemOptions = [];

	public function run()
	: string{
		if (empty($this->position)){
			throw new InvalidConfigException('Menu must be configured with a position');
		}

		$menu_items          = MenuModel::findByPosition($this->position);
		$this->items         = MenuHelper::createTree($menu_items, 1, NULL, $this->itemOptions);
		$this->options['id'] = 'menu-' . $this->position;

		return parent::run();
	}
}