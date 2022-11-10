<?php

namespace modules\themes\spiderboard\widgets;

use yii\base\InvalidConfigException;
use yii\bootstrap5\Html;
use yii\helpers\ArrayHelper;

/**
 * Class Nav
 *
 * @package modules\themes\spiderboard\widgets
 */
class Nav extends \yii\bootstrap5\Nav{

	/**
	 * @var bool
	 */
	public $activateParents = TRUE;

	/**
	 * @var string
	 */
	public $dropdownClass = Nav::class;

	/**
	 * Renders a widget's item.
	 *
	 * @param string|array $item the item to render.
	 *
	 * @return string the rendering result.
	 * @throws InvalidConfigException
	 * @throws \Exception
	 */
	public function renderItem($item)
	: string{
		if (is_string($item)){
			return $item;
		}
		if (!isset($item['label'])){
			throw new InvalidConfigException("The 'label' option is required.");
		}
		$encodeLabel = isset($item['encode']) ? $item['encode'] : $this->encodeLabels;
		$label       = $encodeLabel ? Html::encode($item['label']) : $item['label'];
		$options     = ArrayHelper::getValue($item, 'options', []);
		$items       = ArrayHelper::getValue($item, 'items');
		$url         = ArrayHelper::getValue($item, 'url', "#");
		$linkOptions = ArrayHelper::getValue($item, 'linkOptions', []);
		$disabled    = ArrayHelper::getValue($item, 'disabled', FALSE);
		$active      = $this->isItemActive($item);

		if (empty($items)){
			$items = '';
		}else{
			$linkOptions = ArrayHelper::merge($linkOptions,
				['data-bs-toggle' => 'collapse', 'class' => 'nav-link', 'aria-expanded' => "false"]);

			if (!empty($item['active'])){
				$linkOptions['aria-expanded'] = TRUE;
			}

			if (is_array($items)){
				$url = '#';
				if (!empty($options['id'])){
					$url = "#{$options['id']}-collapse";
				}

				$items = $this->isChildActive($items, $active);
				$items = $this->renderDropdown($items, $item);
			}
		}

		Html::addCssClass($options, 'nav-item');
		Html::addCssClass($linkOptions, 'nav-link');

		if ($disabled){
			ArrayHelper::setValue($linkOptions, 'tabindex', '-1');
			ArrayHelper::setValue($linkOptions, 'aria-disabled', 'true');
			Html::addCssClass($linkOptions, 'disabled');
		}elseif ($this->activateItems && $active){
			Html::addCssClass($linkOptions, 'active');
		}

		return Html::tag('li', Html::a($label, $url, $linkOptions) . $items, $options);
	}

	/**
	 * @param array $items
	 * @param array $parentItem
	 *
	 * @return string
	 * @throws \Exception
	 */
	protected function renderDropdown(array $items, array $parentItem)
	: string{
		$options          = $parentItem['options'] ?? [];
		$options['class'] = ['collapse'];
		if (!empty($options['id'])){
			$options['id'] .= '-collapse';
		}

		if (!empty($parentItem['active'])){
			$options['class'][] = 'show';
		}

		$parentItem['dropdownOptions']['class'] = 'nav flex-column';

		return Html::tag('div', parent::renderDropdown($items, $parentItem), $options);
	}
}