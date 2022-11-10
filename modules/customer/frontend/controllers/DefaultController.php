<?php

namespace modules\customer\frontend\controllers;

use frontend\base\Controller;
use modules\customer\frontend\models\AuthHandler;
use modules\customer\frontend\models\ChangePasswordForm;
use modules\customer\frontend\models\KycForm;
use modules\customer\frontend\models\LoginForm;
use modules\customer\frontend\models\PasswordResetRequestForm;
use modules\customer\frontend\models\RegisterForm;
use modules\customer\frontend\models\ResetPasswordForm;
use modules\customer\models\AuthAction;
use modules\customer\models\Customer;
use modules\customer\models\CustomerAuth;
use modules\customer\models\CustomerBank;
use modules\customer\models\Notification;
use modules\customer\models\SocialLoginSetting;
use modules\customer\Module;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\bootstrap5\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\filters\AjaxFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\Cookie;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * Class CustomerController
 *
 * @package frontend\controllers
 */
class DefaultController extends Controller{

	/**
	 * {@inheritdoc}
	 */
	public function behaviors(){
		$behaviors = [
			'ajax'   => [
				'class' => AjaxFilter::class,
				'only'  => ['validate', 'upload-avatar']
			],
			'access' => [
				'rules' => [
					[
						'actions' => ['register', 'forgot-password', 'new-password', 'auth', 'referral'],
						'allow'   => TRUE,
						'roles'   => ['?'],
					],
					[
						'actions' => ['logout', 'index', 'kyc', 'change-password', 'withdraw-details', 'delete-detail', 'verify-email', 'resend'],
						'allow'   => TRUE,
						'roles'   => ['@'],
					],
					[
						'actions' => ['validate', 'sign-in', 'callback'],
						'allow'   => TRUE
					],
				],
			],
			'verbs'  => [
				'actions' => [
					'validate'      => ['POST'],
					'callback'      => ['POST'],
					'upload-avatar' => ['POST'],
				],
			],
		];

		return ArrayHelper::merge(parent::behaviors(), $behaviors);
	}

	/**
	 * @param \yii\base\Action $action
	 *
	 * @return bool
	 * @throws \yii\web\BadRequestHttpException
	 */
	public function beforeAction($action){
		if ($action->id == 'callback'){
			$this->enableCsrfValidation = FALSE;
		}

		return parent::beforeAction($action);
	}

	/**
	 * @return array
	 */
	public function actions(){
		return [
			'auth' => [
				'class'           => AuthAction::class,
				'successCallback' => [$this, 'onAuthSuccess'],
			],
		];
	}

	/**
	 * @param $client
	 *
	 * @throws \yii\base\Exception
	 */
	public function onAuthSuccess($client){
		(new AuthHandler($client))->handle();
	}

	/**
	 * @return string|Response
	 */
	public function actionIndex(){
		/** @var \modules\customer\models\Customer $user */
		$user = Yii::$app->user->identity;
		if ($user->load($this->request->post()) && $user->save()){
			$this->flash('success', 'Your profile has been updated successfully.');

			return $this->refresh();
		}

		return $this->render('index', [
			'model' => $user
		]);
	}

	/**
	 * @return \yii\web\Response|string
	 * @throws \yii\base\Exception
	 */
	public function actionKyc(){
		$model = new KycForm([
			'verify' => KycForm::SCENARIO_DEFAULT,
			'status' => 100
		]);

		if ($model->load($this->request->post()) && $model->verify == KycForm::SCENARIO_DEFAULT){
			$model->front_image = UploadedFile::getInstance($model, 'front_image');
			$model->back_image  = UploadedFile::getInstance($model, 'back_image');

			if ($model->front_image && $model->back_image && $model->update()){
				if (!empty($model->front_image)){
					$this->flash('success', 'Your KYC has been updated successfully.');
				}else{
					$this->flash('success', 'Your profile has been updated successfully.');
				}
			}

			return $this->refresh();
		}

		$user = $model->user;
		if (!empty($user->kyc)){
			$model->front_image = $user->kyc->frontImage;
			$model->back_image  = $user->kyc->backImage;
			$model->status      = $user->kyc->status;
			$model->reason      = $user->kyc->reason;
		}

		if (empty($user->verify['email']) || $user->verify['email'] !== TRUE){
			$model_email = new KycForm([
				'scenario' => KycForm::SCENARIO_EMAIL,
				'email'    => $user->email,
				'verify'   => KycForm::SCENARIO_EMAIL
			]);

			if (!empty($model_email->user->verify['email']['code'])){
				$model_email->scenario = KycForm::SCENARIO_EMAIL_CODE;
			}

			if ($model_email->load($this->request->post()) && $model_email->verify == KycForm::SCENARIO_EMAIL){
				if ($model_email->verify()){
					if ($model_email->scenario === KycForm::SCENARIO_EMAIL){
						$this->flash('success', 'Check your email for further instructions');
					}else{
						$this->flash('success', 'Your email has been verified successfully.');
					}

					return $this->refresh();
				}

				$this->flash('error',
					'Sorry, we are unable to verify your email address right now. Please try again.');
			}
		}

		return $this->render('kyc', [
			'model'       => $model,
			'model_email' => $model_email ?? NULL
		]);
	}

	/**
	 * @return string|\yii\web\Response
	 * @throws \yii\base\Exception
	 */
	public function actionChangePassword(){
		$model = new ChangePasswordForm();

		if ($model->load($this->request->post()) && $model->change()){
			$this->flash('success', 'New password saved. Please sign in to continue.');

			return $this->refresh();
		}

		return $this->render('change-password', [
			'model' => $model
		]);
	}

	/**
	 * @return array|string|\yii\web\Response
	 */
	public function actionWithdrawDetails(){
		$model = new CustomerBank([
			'customer_id' => Yii::$app->user->getId(),
		]);

		if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())){
			$validate = ActiveForm::validate($model);
			if (!empty($validate)){
				Yii::$app->response->format = Response::FORMAT_JSON;

				return ['validate' => $validate];
			}elseif ($model->save()){
				$this->flash('success', 'Bank successfully created.');

				return $this->refresh();
			}
		}

		$query = CustomerBank::find()
		                     ->andWhere(['customer_id' => Yii::$app->user->getId()]);

		$details = new ActiveDataProvider([
			'query' => $query,
		]);

		return $this->render('withdraw-details', [
			'details' => $details,
			'model'   => $model
		]);
	}

	/**
	 * @param $id
	 *
	 * @return \yii\web\Response
	 * @throws \Throwable
	 * @throws \yii\db\StaleObjectException
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function actionDeleteDetail($id){
		$this->findModelBank($id)->delete();
		$this->flash('success', Yii::t('customer', 'Bank has been deleted successfully'));

		return $this->back();
	}

	/**
	 * @param $id
	 *
	 * @return CustomerBank
	 * @throws \yii\web\NotFoundHttpException
	 */
	protected function findModelBank($id){
		if (($model = CustomerBank::findOne($id)) !== NULL){
			return $model;
		}

		throw new NotFoundHttpException(Yii::t('common', 'The requested page does not exist.'));
	}


	/**
	 * @return string
	 */
	public function actionValidate(){
		Yii::$app->response->format = Response::FORMAT_JSON;

		return $this->renderAjax('_validate');
	}

	/**
	 * @return string|\yii\web\Response
	 * @throws \Throwable
	 */
	public function actionSignIn(){
		if (!Yii::$app->user->isGuest){
			return $this->redirect(['index']);
		}

		$this->view->title = Yii::t('customer', 'Sign In');

		$model = new LoginForm();
		AuthHandler::return();

		if ($model->load($this->request->post()) && $model->login()){
			return $this->goBack();
		}

		$model->password = '';

		return $this->render('sign-in', ['model' => $model]);
	}

	/**
	 * @return string|\yii\web\Response
	 * @throws \yii\base\Exception
	 */
	public function actionRegister(){
		$model = new RegisterForm();
		AuthHandler::return();

		if ($model->load($this->request->post()) && ($user = $model->register())){
			if (Yii::$app->getUser()->login($user)){
				return $this->goBack();
			}
		}

		$this->view->title = Yii::t('customer', 'Register');

		$this->view->registerMetaTag([
			'name'    => 'description',
			'content' => Yii::t('customer',
				"BK368 Asia's most prestigious bookie - Prestigious, fast and secure, 5 minutes deposit and withdrawal")
		]);

		return $this->render('register', [
			'model' => $model
		]);
	}

	/**
	 * Logs out the current user.
	 *
	 * @return mixed
	 */
	public function actionLogout(){
		Yii::$app->user->logout();

		return $this->goHome();
	}

	/**
	 * Requests password reset.
	 *
	 * @return mixed
	 * @throws \yii\base\Exception
	 */
	public function actionForgotPassword(){
		$model = new PasswordResetRequestForm();
		if ($model->load($this->request->post()) && $model->validate()){
			if ($model->sendEmail()){
				$this->flash('success',
					'Check your email for further instructions.');

				return $this->redirect(['sign-in']);
			}

			$this->flash('error',
				'Sorry, we are unable to reset password for the provided email address.');
		}

		$this->view->title = $this->t('Forgot password');

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
		try{
			$model = new ResetPasswordForm($token);
		}catch (InvalidArgumentException $exception){
			throw new BadRequestHttpException($exception->getMessage());
		}

		if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()){
			$this->flash('success', 'New password saved. Please sign in to continue.');

			return $this->redirect(['sign-in']);
		}

		$this->view->title = $this->t('Set new password');

		return $this->render('new-password', [
			'model' => $model,
		]);
	}

	/**
	 * @return string[]
	 * @throws \yii\web\NotFoundHttpException|\yii\base\Exception
	 */
	public function actionCallback(){
		$signed_request = $this->request->post('signed_request');
		if (!empty($signed_request)){
			if ($data = $this->parseSignedRequest($signed_request)){
				$user_id = $data['user_id'];

				$auth = CustomerAuth::findOne(['source' => 'facebook', 'source_id' => $user_id]);
				if ($auth){
					$this->response->format = Response::FORMAT_JSON;

					$token      = md5($auth->id);
					$status_url = Yii::$app->urlManager->createAbsoluteUrl(['/customer/default/deletion', 'token' => $token]);

					return [
						'url'               => $status_url,
						'confirmation_code' => $token
					];
				}
			}
		}

		throw new NotFoundHttpException("Invalid request");
	}

	/**
	 * @param $signed_request
	 *
	 * @return mixed|null
	 */
	private function parseSignedRequest($signed_request){
		[$encoded_sig, $payload] = explode('.', $signed_request, 2);
		$setting = new SocialLoginSetting();
		$setting->getValues();
		$secret       = $setting->facebook_secret;
		$sig          = $this->base64UrlDecode($encoded_sig);
		$data         = Json::decode($this->base64UrlDecode($payload), TRUE);
		$expected_sig = hash_hmac('sha256', $payload, $secret, TRUE);
		if ($sig !== $expected_sig){
			Yii::error('Facebook', 'Bad Signed JSON signature!');

			return NULL;
		}

		return $data;
	}

	/**
	 * @param $input
	 *
	 * @return false|string
	 */
	private function base64UrlDecode($input){
		return base64_decode(strtr($input, '-_', '+/'));
	}

	/**
	 * @param $code
	 *
	 * @return \yii\web\Response
	 */
	public function actionReferral($code){
		$cookies = Yii::$app->response->cookies;
		$cookies->add(new Cookie([
			'name'   => Module::COOKIE_REF,
			'value'  => $code,
			'expire' => time() + 30 * 86400
		]));

		return $this->redirect(['/customer/default/register']);
	}

	/**
	 * Resets password.
	 *
	 * @param string $token
	 *
	 * @return mixed
	 * @throws \yii\base\Exception
	 */
	public function actionVerifyEmail($token){
		$model = Customer::find()
		                 ->andWhere(['LIKE', 'verify', '"' . $token . '"'])
		                 ->one();

		if (!empty($model)){
			$verify          = $model->verify;
			$verify['email'] = TRUE;
			$model->verify   = $verify;
			if ($model->save()){
				$this->flash('success', 'Your email has been verified successfully.');
			}


			return $this->redirect(['sign-in']);
		}

		throw new InvalidConfigException(Yii::t('customer', 'Your verification link has expired.'));
	}

	/**
	 * @return \yii\web\Response
	 */
	public function actionResend(){
		/**@var  \modules\customer\frontend\models\CustomerIdentity $user */
		$user = Yii::$app->user->identity;
		if (!empty($user->verify['email']['code'])){
			$sent = Notification::verifyEmail($user, $user->verify['email']['code'],
				$user->verify['email']['token']);

			if ($sent){
				$this->flash('success',
					'Verification Code has been sent. Please check your email for further instructions.');

				return $this->redirect(['kyc']);
			}
		}

		$this->flash('error',
			'Sorry, we are unable to send email right now. Please try again.');

		return $this->redirect(['kyc']);
	}
}