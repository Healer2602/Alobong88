<?php

namespace modules\matrix\backend\controllers;

use backend\base\Controller;
use modules\matrix\models\Setting;
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
						'permissions' => ['matrix setting'],
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

		return $this->render('index', [
			'model' => $model
		]);
	}
}