<?php

namespace modules\agent\frontend\controllers;

use frontend\base\Controller;
use modules\agent\Module;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Cookie;

/**
 * Class DefaultController
 *
 * @package modules\agent\frontend
 */
class DefaultController extends Controller{

	/**
	 * {@inheritdoc}
	 */
	public function behaviors(){
		$behaviors = [
			'access' => [
				'rules' => [
					[
						'actions' => ['index'],
						'allow'   => TRUE
					]
				],
			]
		];

		return ArrayHelper::merge(parent::behaviors(), $behaviors);
	}


	/**
	 * @param $code
	 *
	 * @return \yii\web\Response
	 */
	public function actionIndex($code){
		$cookies = Yii::$app->response->cookies;
		$cookies->add(new Cookie([
			'name'   => Module::COOKIE_REF,
			'value'  => $code,
			'expire' => time() + 30 * 86400
		]));

		return $this->redirect(['/customer/default/register']);
	}

}