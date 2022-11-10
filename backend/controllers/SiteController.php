<?php

namespace backend\controllers;

use backend\models\ForgotPasswordForm;
use backend\models\LoginForm;
use backend\models\ResetPasswordForm;
use backend\models\UserForm;
use common\models\AuditTrail;
use Yii;
use yii\base\InvalidArgumentException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;

/**
 * Site controller
 */
class SiteController extends Controller{

	public $layout = 'login';

	/**
	 * {@inheritdoc}
	 */
	public function behaviors(){
		return [
			'access' => [
				'class' => AccessControl::class,
				'rules' => [
					[
						'actions' => ['login', 'error', 'forgot-password', 'new-password'],
						'allow'   => TRUE,
					],
					[
						'actions' => ['logout', 'index', 'my-profile'],
						'allow'   => TRUE,
						'roles'   => ['@'],
					],
				],
			],
			'verbs'  => [
				'class'   => VerbFilter::class,
				'actions' => [
					'logout' => ['post'],
				],
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function actions(){
		return [
			'error' => [
				'class' => 'yii\web\ErrorAction',
			],
		];
	}

	/**
	 * Displays homepage.
	 *
	 * @return string
	 */
	public function actionIndex(){
		$this->layout = 'main';

		return $this->render('index');
	}

	/**
	 * Login action.
	 *
	 * @return string
	 */
	public function actionLogin(){
		if (!Yii::$app->user->isGuest){
			return $this->goHome();
		}

		$model = new LoginForm();
		if ($model->load(Yii::$app->request->post()) && $model->login()){
			AuditTrail::log(Yii::t('common', 'Login'),
				Yii::t('common', 'Logged in successfully'),
				Yii::t('common', 'Users'));

			return $this->goBack();
		}

		$model->password = '';

		return $this->render('login', [
			'model' => $model,
		]);
	}

	/**
	 * Logout action.
	 *
	 * @return string
	 */
	public function actionLogout(){
		Yii::$app->user->logout();
		AuditTrail::log(Yii::t('common', 'Logout'),
			Yii::t('common', 'Logged out successfully'),
			Yii::t('common', 'Users'));

		return $this->goHome();
	}

	/**
	 * @return string
	 * @throws \yii\base\Exception
	 */
	public function actionMyProfile(){
		$this->layout = 'main';

		/**@var \backend\models\Staff $user */
		$user  = Yii::$app->user->identity;
		$model = new UserForm([
			'id'       => $user->id,
			'username' => $user->username,
			'name'     => $user->name,
			'email'    => $user->email,
			'scenario' => UserForm::SCENARIO_CHANGE_PASSWORD
		]);

		if ($model->load(Yii::$app->request->post()) && $model->changepass()){
			Yii::$app->session->setFlash('success', 'Your profile has been updated successfully.');

			return $this->refresh();
		}

		return $this->render('my-profile', [
			'model' => $model
		]);
	}

	/**
	 * Requests password reset.
	 *
	 * @return mixed
	 * @throws \yii\base\Exception
	 */
	public function actionForgotPassword(){
		if (!Yii::$app->user->isGuest){
			return $this->redirect(['index']);
		}

		$model = new ForgotPasswordForm();
		if ($model->load(Yii::$app->request->post()) && $model->validate()){
			if ($model->sendEmail()){
				Yii::$app->session->addFlash('success',
					'Check your email for further instructions.');

				return $this->redirect(['login']);
			}

			Yii::$app->session->addFlash('error',
				'Sorry, we are unable to reset password for the provided email address.');
		}

		return $this->render('forgot-password', [
			'model' => $model,
		]);
	}

	/**
	 * Resets password.
	 *
	 * @param string $token
	 *
	 * @return mixed
	 * @throws \yii\base\Exception
	 * @throws \yii\web\BadRequestHttpException
	 */
	public function actionNewPassword($token){
		if (!Yii::$app->user->isGuest){
			return $this->redirect(['index']);
		}

		try{
			$model = new ResetPasswordForm($token);
		}catch (InvalidArgumentException $exception){
			throw new BadRequestHttpException($exception->getMessage());
		}

		if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()){
			Yii::$app->session->addFlash('success',
				'New password saved. Please sign in to continue.');

			return $this->redirect(['login']);
		}

		return $this->render('new-password', [
			'model' => $model,
		]);
	}
}
