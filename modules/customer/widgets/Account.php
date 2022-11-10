<?php

namespace modules\customer\widgets;

use Yii;
use yii\bootstrap5\Widget;

/**
 * Class Info
 *
 * @package modules\customer\widgets
 */
class Account extends Widget{

	/**
	 * @return string
	 */
	public function run(){
		parent::run();

		if (!Yii::$app->user->isGuest){
			$user = Yii::$app->user->identity;
		}else{
			$user = NULL;
		}

		return $this->render('account', [
			'model' => $user
		]);
	}
}