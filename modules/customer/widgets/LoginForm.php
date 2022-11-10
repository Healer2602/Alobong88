<?php

namespace modules\customer\widgets;

use modules\customer\frontend\models\LoginForm as Model;
use yii\bootstrap5\Widget;

/**
 * Class LoginForm
 *
 * @package modules\customer\blocks
 */
class LoginForm extends Widget{

	/**
	 * @var Model
	 */
	public $model = NULL;

	/**
	 * @inheritDoc
	 */
	public function run(){
		if (empty($this->model)){
			$this->model = new Model();
		}

		return $this->render('login', [
			'model' => $this->model
		]);
	}
}