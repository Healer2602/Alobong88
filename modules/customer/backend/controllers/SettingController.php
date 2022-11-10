<?php

namespace modules\customer\backend\controllers;

use backend\base\Controller;
use modules\customer\models\Setting;
use modules\customer\models\SocialLoginSetting;
use yii\helpers\ArrayHelper;

/**
 * Class SettingController
 *
 * @package modules\customer\backend\controllers
 */
class SettingController extends Controller{

	/**
	 * @return array
	 */
	public function behaviors(){
		$behaviors = [
			'access' => [
				'rules' => [
					[
						'allow'       => TRUE,
						'actions'     => ['index'],
						'permissions' => ['customer setting'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['social-login'],
						'permissions' => ['customer social-login'],
					],
				]
			]
		];

		return ArrayHelper::merge(parent::behaviors(), $behaviors);
	}

	/**
	 *
	 * @return string|\yii\web\Response
	 * @throws \Throwable
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\db\Exception
	 */
	public function actionIndex(){
		$model = new Setting();
		$model->getValues();

		if ($model->load($this->request->post()) && $model->save()){
			$this->flash('success', 'The setting updated successfully');

			return $this->refresh();
		}

		$model->currencies = $model->listCurrency;

		return $this->render('index', [
			'model' => $model
		]);
	}

	/**
	 * @return string|\yii\web\Response
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\db\Exception
	 */
	public function actionSocialLogin(){
		$model = new SocialLoginSetting();
		$model->getValues();

		if ($model->load($this->request->post()) && $model->save()){
			$this->flash('success', 'The setting updated successfully');

			return $this->refresh();
		}

		return $this->render('social-login', [
			'model' => $model
		]);
	}
}