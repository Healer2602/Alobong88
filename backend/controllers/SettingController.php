<?php

namespace backend\controllers;

use backend\base\Controller;
use backend\models\Setting;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * SettingController implements the CRUD actions for Setting model.
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
						'actions'     => ['index', 'clear-cache'],
						'permissions' => ['setting'],
					]
				]
			]
		];

		return ArrayHelper::merge(parent::behaviors(), $behaviors);
	}

	/**
	 * @return string
	 */
	public function actionIndex(){
		$model    = new Setting();
		$settings = $model->list();

		return $this->render('index', [
			'settings' => $settings
		]);
	}

	/**
	 * @return \yii\web\Response
	 */
	public function actionClearCache(){
		Yii::$app->cache->flush();
		$this->flash('success', 'All caches were cleared successfully.');

		if ($referer = Yii::$app->request->referrer){
			return $this->redirect($referer);
		}

		return $this->redirect(['index']);
	}
}
