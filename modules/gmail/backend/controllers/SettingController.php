<?php

namespace modules\gmail\backend\controllers;

use backend\base\Controller;
use modules\gmail\models\GoogleSetting;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class SettingController
 *
 * @package modules\gmail\backend\controllers
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
						'permissions' => ['setting gmail'],
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
		$model = $this->findModel();

		if ($model->load($this->request->post())){
			$action = $this->request->post('submit');
			if (empty($action) && $model->save()){
				$this->flash('success', 'The setting updated successfully');
			}

			if (!empty($action)){
				if ($action == $model::ACTION_CONNECT){
					$model->connect();
				}elseif ($model->remove()){
					$this->flash('success', 'Google Account has been removed successfully');
				}
			}

			return $this->refresh();
		}

		return $this->render('index', [
			'model' => $model
		]);
	}

	/**
	 * @return \yii\web\Response
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\db\Exception
	 */
	public function actionAuth(){
		if ($code = $this->request->get('code')){
			$model            = $this->findModel();
			$model->auth_code = $code;
			if ($model->save() && $model->setToken($code)){
				$this->flash('success', 'Google Account has been connected successfully');
			}
		}

		return $this->redirect(['index']);
	}

	/**
	 * @return \yii\web\Response
	 */
	public function actionTest(){
		if ($email = $this->request->post('email')){
			$sent = Yii::$app->mailer->compose()
			                         ->setTo($email)
			                         ->setSubject('Test email from ' . Yii::$app->name)
			                         ->setTextBody('Test email from ' . Yii::$app->name)
			                         ->setHtmlBody('Test email from ' . Yii::$app->name)
			                         ->send();

			if ($sent){
				$this->flash('success', 'Email has been sent.');
			}else{
				$this->flash('success', 'Email has not been sent.');
			}
		}

		return $this->redirect(['index']);
	}

	/**
	 * @return \modules\gmail\models\GoogleSetting
	 */
	private function findModel(){
		$model = new GoogleSetting();
		$model->getValues();

		return $model;
	}
}