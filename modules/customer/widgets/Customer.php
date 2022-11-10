<?php

namespace modules\customer\widgets;

use Yii;
use yii\bootstrap5\Widget;

/**
 * Customer Widget for Frontend
 */
class Customer extends Widget{

	/**
	 * @return string
	 * @throws \Exception
	 */
	public function run(){
		parent::run();

		if (Yii::$app->user->isGuest){
			return LoginForm::widget();
		}

		return Account::widget();
	}
}