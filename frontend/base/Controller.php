<?php

namespace frontend\base;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * Class Controller
 *
 * @package frontend\base
 */
class Controller extends \yii\web\Controller{

	public $translation;

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
					'delete' => ['POST'],
				],
			],
			'access' => [
				'class'        => AccessControl::class,
				'denyCallback' => [$this, 'denyCallback'],
			]
		];
	}

	/**
	 * @param $key
	 * @param $value
	 * @param array $params
	 */
	public function flash($key, $value, $params = []){
		if (is_array($value)){
			Yii::$app->session->setFlash($key, $value);
		}else{
			Yii::$app->session->setFlash($key, $this->t($value, $params));
		}
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

		if (!empty($this->module->id) && !empty($translation[$this->module->id])){
			$category = $this->module->id;
		}elseif (!empty($translation[Yii::$app->id])){
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
	 * @throws \yii\base\InvalidConfigException
	 */
	public function denyCallback(){
		if (Yii::$app->user->isGuest){
			$this->flash('danger', Yii::t('common', 'Please login to proceed.'));
		}
		Yii::$app->user->setReturnUrl(Yii::$app->request->getUrl());

		return $this->redirect(['/customer/default/sign-in']);
	}
}