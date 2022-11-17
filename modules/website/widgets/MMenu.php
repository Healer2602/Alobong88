<?php

namespace modules\website\widgets;

use frontend\base\MenuHelper;
use modules\website\assets\MMenuAsset;
use modules\website\models\Menu as MenuModel;
use Yii;
use yii\base\InvalidConfigException;
use yii\bootstrap5\Html;
use yii\bootstrap5\Widget;
use yii\helpers\ArrayHelper;

/**
 * Class MMenu
 *
 * @package frontend\widgets
 */
class MMenu extends Widget{

	public $position;
	public $button_label;
	public $button_options = [];
	public $items = [];
	public $active = FALSE;
	public $page_selector = '#page';

	protected $route;
	protected $params;

	/**
	 * Initializes the widget.
	 */
	public function init(){
		parent::init();
		if ($this->route === NULL && Yii::$app->controller !== NULL){
			$this->route = Yii::$app->controller->getRoute();
		}
		if ($this->params === NULL){
			$this->params = Yii::$app->request->getQueryParams();
		}
	}

	/**
	 * @return string
	 * @throws \Throwable
	 * @throws \yii\base\InvalidConfigException
	 */
	public function run(){
		if (empty($this->position)){
			throw new InvalidConfigException('Menu must be configured with a position');
		}

		MMenuAsset::register($this->view);

		$html     = '';
		$this->id = "mmenu-{$this->position}-{$this->id}";

		$menu_items  = MenuModel::findByPosition($this->position);
		$this->items = MenuHelper::createTree($menu_items, 1, NULL);

		if (!empty($this->items)){
			$this->renderJs();

			$html = Html::beginTag('div', ['class' => 'fr-menu']);
			$html .= Html::a($this->button_label, "#{$this->id}",
				['class' => $this->button_options]);
			$html .= Html::beginTag('nav', ArrayHelper::merge($this->options, ['id' => $this->id]));
			$html .= $this->renderItems();
			$html .= Html::endTag('nav');
			$html .= Html::endTag('div');
		}

		return $html;
	}

	/**
	 * Renders widget items.
	 *
	 * @return string
	 */
	public function renderItems(){
		$items = [];
		foreach ($this->items as $item){
			if (isset($item['visible']) && !$item['visible']){
				continue;
			}
			$items[] = $this->renderItem($item);
		}

		return Html::tag('ul', implode("\n", $items));
	}

	/**
	 * @param $item
	 *
	 * @return string
	 */
	public function renderItem($item){
		$item_options          = [];
		$list_options['class'] = [];

		if ($active = $this->isItemActive($item)){
			$item_options['class'] = ['active'];
		}

		if (!empty($item['params']['style'])){
			$list_options['class'] = [$item['params']['style']];
		}

		if (!empty($item['params']['new_tab'])){
			$item_options['target'] = '_blank';
		}

		if (!empty($item['params']['no_follow'])){
			$item_options['rel'] = 'nofollow';
		}

		if (empty($item['id'])){
			$item['id'] = '0';
		}

		if (empty($item['items'])){
			return Html::tag('li', Html::a($item['label'], $item['url'], $item_options),
				['class' => "menu-item-{$item['id']}"]);
		}

		$list_options['class'][] = 'parent';
		$list_options['class'][] = "menu-item-{$item['id']}";

		$html = Html::beginTag('li', $list_options);
		$html .= Html::a($item['label'], $item['url'], $item_options);
		$html .= $this->renderSubItems($item);

		return $html . Html::endTag('li');
	}

	/**
	 * @param $item
	 *
	 * @return string
	 */
	public function renderSubItems($item){
		$children  = $item['items'];
		$sub_items = [];

		foreach ($children as $item){
			if (isset($item['visible']) && !$item['visible']){
				continue;
			}
			$sub_items[] = $this->renderItem($item);
		}

		return Html::tag('ul', implode("\n", $sub_items), ['class' => 'sub-menu']);
	}

	/**
	 * Checks whether a menu item is active.
	 * This is done by checking if [[route]] and [[params]] match that specified in the `url`
	 * option of the menu item. When the `url` option of a menu item is specified in terms of an
	 * array, its first element is treated as the route for the item and the rest of the elements
	 * are the associated parameters. Only when its route and parameters match [[route]] and
	 * [[params]], respectively, will a menu item be considered active.
	 *
	 * @param array $item the menu item to be checked
	 *
	 * @return bool whether the menu item is active
	 */
	protected function isItemActive($item){
		if (!$this->active){
			return FALSE;
		}

		if (isset($item['active'])){
			return ArrayHelper::getValue($item, 'active', FALSE);
		}

		if (isset($item['url']) && is_array($item['url']) && isset($item['url'][0])){
			$route = $item['url'][0];
			if ($route[0] !== '/' && Yii::$app->controller){
				$route = Yii::$app->controller->module->getUniqueId() . '/' . $route;
			}
			if (ltrim($route, '/') !== $this->route){
				return FALSE;
			}

			unset($item['url']['#']);
			if (count($item['url']) > 1){
				$params = $item['url'];
				unset($params[0]);
				foreach ($params as $name => $value){
					if ($value !== NULL && (!isset($this->params[$name]) || $this->params[$name] != $value)){
						return FALSE;
					}
				}
			}

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @inheritDoc
	 */
	protected function renderJs(){
		$js = <<<JS
		$("#$this->id").mmenu({
            navbar: {
                title: "$this->button_label"
            },
            navbars: [
                {
                    content: ['prev', 'title', 'close']
                }
            ]
        }, {
		     offCanvas: {
	            clone: true,
	            page: {
	                selector: "$this->page_selector"
	            }
	        }
        });
JS;
		$this->view->registerJs($js);
	}
}