<?php

namespace modules\wallet\backend\controllers;

use backend\base\Controller;
use modules\wallet\models\Setting;
use yii\helpers\ArrayHelper;

/**
 * Class SettingController
 *
 * @package modules\wallet\backend\controllers
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
						'permissions' => ['wallet setting'],
					],
				]
			]
		];

		return ArrayHelper::merge(parent::behaviors(), $behaviors);
	}

	/**
	 * @return string|\yii\web\Response
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

		return $this->render('index', [
			'model' => $model
		]);
	}
}