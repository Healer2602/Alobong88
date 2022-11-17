<?php

namespace modules\website\widgets;

use yii\bootstrap5\Html;
use yii\helpers\ArrayHelper;

/**
 * Class FMenu
 *
 * @package modules\product\frontend
 */
class FMenu extends MMenu{

	public $items = [];
	public $active = TRUE;

	/**
	 * @return string
	 * @throws \Throwable
	 */
	public function run(){
		$html = '';

		if (!empty($this->items)){
			$html .= Html::beginTag('nav', ArrayHelper::merge($this->options, ['id' => $this->id]));
			$html .= $this->renderItems();
			$html .= Html::endTag('nav');
		}

		return $html;
	}
}