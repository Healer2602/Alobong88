<?php

namespace frontend\widgets;

use modules\block\BlockWidget as WidgetModel;
use yii\base\Widget;

/**
 * Class Footer
 *
 * @package frontend\widgets
 */
class Footer extends Widget{

	const POSITION = 'footer';

	/**
	 * @inheritDoc
	 */
	public function run(){
		$widgets = WidgetModel::findByPosition(self::POSITION)->limit(- 1)->all();

		return $this->render('//widgets/footer', [
			'widgets' => $widgets,
		]);
	}

}