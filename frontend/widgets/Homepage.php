<?php

namespace frontend\widgets;

use modules\block\BlockWidget as WidgetModel;
use yii\base\Widget;

/**
 * Class Homepage
 *
 * @package frontend\widgets
 */
class Homepage extends Widget{

	const POSITION = 'homepage';

	/**
	 * @inheritDoc
	 */
	public function run(){
		$widgets = WidgetModel::findByPosition(self::POSITION)->limit(- 1)->all();

		return $this->render('//widgets/homepage', [
			'widgets' => $widgets,
		]);
	}

}