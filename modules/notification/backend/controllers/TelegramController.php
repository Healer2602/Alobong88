<?php

namespace modules\notification\backend\controllers;

use backend\base\Controller;
use modules\notification\models\TelegramSetting;
use yii\helpers\ArrayHelper;

/**
 * Class SettingController
 *
 * @package modules\notification\backend\controllers
 */
class TelegramController extends Controller{

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
						'permissions' => ['notification telegram'],
					]
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
		$model = new TelegramSetting();
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