<?php

namespace backend\base;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * Class Controller
 *
 * @package backend\base
 */
class Controller extends \yii\web\Controller{

	/**
	 * @var array
	 */
	public $params;

	/**
	 * @inheritDoc
	 */
	public function init(){
		parent::init();

		$this->params = Yii::$app->params;
	}

	/**
	 * @return array
	 */
	public function behaviors(){

		return [
			'verbs'  => [
				'class'   => VerbFilter::class,
				'actions' => [
					'delete' => ['post'],
				],
			],
			'access' => [
				'class'        => AccessControl::class,
				'denyCallback' => [$this, 'denyCallback'],
			]
		];
	}

	/**
	 * @param $action
	 *
	 * @return bool
	 * @throws \yii\web\BadRequestHttpException
	 */
	public function beforeAction($action){
		if (Yii::$app->id != 'admin'){
			$this->denyCallback();

			return FALSE;
		}

		return parent::beforeAction($action);
	}

	/**
	 * @param $key
	 * @param $value
	 * @param array $params
	 */
	public function flash($key, $value, $params = []){
		if (is_array($value)){
			return Yii::$app->getSession()->setFlash($key, $value);
		}

		return Yii::$app->getSession()->setFlash($key, $this->t($value, $params));
	}

	/**
	 * @param $message
	 * @param array $params
	 *
	 * @return string
	 */
	public function t($message, $params = []){
		$translation = Yii::$app->getI18n()->translations;
		$category    = 'common';

		if (!empty($translation[Yii::$app->id])){
			$category = Yii::$app->id;
		}

		return Yii::t($category, $message, $params);
	}

	/**
	 * @param null $hash
	 *
	 * @return \yii\web\Response
	 */
	public function back($hash = NULL){
		if ($referrer = $this->request->referrer){
			if (!empty($hash)){
				return $this->redirect($referrer . '#' . $hash);
			}

			return $this->redirect($referrer);
		}

		return $this->redirect(['index', '#' => $hash]);
	}

	/**
	 * @return \yii\web\Response
	 */
	public function denyCallback(){
		$this->flash('danger', 'You are not allowed to perform this action.');

		return $this->redirect(['/site/index']);
	}

	/**
	 * @return array
	 */
	public function filtering(){
		return array_filter($this->request->get(), function ($data){
			return $data !== '';
		});
	}
}