<?php

namespace modules\customer\widgets;

use Yii;
use yii\bootstrap5\Widget;

/**
 * Class Menu
 */
class Menu extends Widget{

	/**
	 * @return string
	 */
	public function run(){
		parent::run();

		$this->view->params['bodyClasses'] = 'page customer-page';

		return $this->render('menu', [
			'user' => Yii::$app->user->identity
		]);
	}
}