<?php

namespace modules\website\backend\controllers;

use backend\base\Controller;
use modules\website\models\HtaccessSetting;
use modules\website\models\WebsiteSetting;
use yii\helpers\ArrayHelper;

/**
 * Class SettingController
 *
 * @package modules\website\backend\controllers
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
						'permissions' => ['setting website'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['htaccess'],
						'permissions' => ['setting website htaccess'],
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
		$model = new WebsiteSetting();
		$model->getValues();

		if ($model->load($this->request->post()) && $model->save()){
			$this->flash('success', 'The setting updated successfully');

			return $this->refresh();
		}

		return $this->render('index', [
			'model' => $model
		]);
	}

	/**
	 * @return string|\yii\web\Response
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\db\Exception
	 */
	public function actionHtaccess(){
		$model = new HtaccessSetting();
		$model->getValues();

		if ($model->load($this->request->post()) && $model->save()){
			$this->flash('success', 'The .htaccess file updated successfully');

			return $this->refresh();
		}

		return $this->render('htaccess', [
			'model' => $model
		]);
	}
}