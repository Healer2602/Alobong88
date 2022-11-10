<?php

namespace modules\agent\frontend\controllers;

use frontend\base\Controller;
use modules\agent\api\models\Agent;
use modules\agent\api\models\Report;
use yii\base\InvalidArgumentException;
use yii\helpers\ArrayHelper;
use yii\web\Response;

/**
 * Class DefaultController
 *
 * @package modules\agent\api
 */
class ApiController extends Controller{

	public $enableCsrfValidation = FALSE;

	/**
	 * {@inheritdoc}
	 */
	public function behaviors(){
		$behaviors = [
			'access' => [
				'rules' => [
					[
						'allow' => TRUE
					]
				]
			],
			'verbs'  => [
				'actions' => [
					'index'   => ['POST'],
					'summary' => ['POST'],
				]
			]
		];

		return ArrayHelper::merge(parent::behaviors(), $behaviors);
	}

	/**
	 * @param $action
	 *
	 * @return bool
	 * @throws \yii\web\BadRequestHttpException
	 */
	public function beforeAction($action){
		$this->response->format = Response::FORMAT_JSON;
		$this->request->parsers = [
			'application/json' => 'yii\web\JsonParser',
		];

		return parent::beforeAction($action);
	}

	/**
	 * @return array
	 * @throws \yii\base\Exception
	 */
	public function actionIndex(){
		$model = new Agent();

		if ($model->load($this->request->post(), '') && ($data = $model->store())){
			return [
				'code' => 200,
				'data' => $data
			];
		}

		if ($errors = $model->getErrorSummary(TRUE)){
			$message = implode("\n\r", $errors);
		}else{
			$message = 'Unknown';
		}

		throw new InvalidArgumentException($message);
	}

	/**
	 * @return array
	 */
	public function actionSummary(){
		$model = new Report();

		if ($model->load($this->request->post(),
				'') && $model->validate() && ($data = $model->getSummary())){
			return [
				'code' => 200,
				'data' => $data
			];
		}

		if ($errors = $model->getErrorSummary(TRUE)){
			$message = implode("\n\r", $errors);
		}else{
			$message = 'Unknown';
		}

		throw new InvalidArgumentException($message);
	}

}