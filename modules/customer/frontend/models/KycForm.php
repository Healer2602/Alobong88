<?php

namespace modules\customer\frontend\models;

use common\base\Status;
use modules\customer\models\Customer;
use modules\customer\models\Kyc;
use modules\customer\models\Notification;
use modules\customer\models\Setting;
use Yii;
use yii\base\Model;

/**
 * KycForm form
 *
 * @property-read string $frontImage
 * @property-read string $backImage
 * @property-read \modules\customer\frontend\models\CustomerIdentity $user
 */
class KycForm extends Model{

	const SCENARIO_EMAIL = 'verify_email';

	const SCENARIO_EMAIL_CODE = 'verify_email_code';

	public $front_image;
	public $back_image;
	public $status = NULL;
	public $reason;
	public $verify;

	public $email;
	public $email_code;

	public $phone;

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			['verify', 'string'],
			['verify', 'required'],

			['phone', 'string'],
			['email', 'email'],
			['status', 'integer'],
			[['front_image', 'back_image'], 'file', 'extensions' => 'png, jpeg, jpg', 'skipOnError' => FALSE, 'skipOnEmpty' => TRUE],
			['front_image', 'required', 'when' => function (){
				return !empty($this->back_image);
			}, 'whenClient'                    => "function(attribute, value){return $('#ekyc-front').val().length == 0 && $('#ekyc-back').val().length > 0}"],
			['back_image', 'required', 'when' => function (){
				return !empty($this->front_image);
			}, 'whenClient'                   => "function(attribute, value){return $('#ekyc-back').val().length == 0 && $('#ekyc-front').val().length > 0}"],

			['email', 'required', 'on' => [self::SCENARIO_EMAIL, self::SCENARIO_EMAIL_CODE]],
			['email_code', 'required', 'on' => self::SCENARIO_EMAIL_CODE],
			['email', 'validateEmail', 'on' => self::SCENARIO_EMAIL],
			['email_code', 'validateEmailCode', 'on' => self::SCENARIO_EMAIL_CODE],
		];
	}

	/**
	 * @return array
	 */
	public function scenarios(){
		$scenarios = parent::scenarios();

		$scenarios[self::SCENARIO_EMAIL] = $scenarios[self::SCENARIO_EMAIL_CODE] = ['email', 'email_code'];

		return $scenarios;
	}

	/**
	 * @return array
	 */
	public function attributeLabels(){
		return [
			'email'       => Yii::t('customer', 'Email'),
			'front_image' => Yii::t('customer', 'Front ID'),
			'back_image'  => Yii::t('customer', 'Back ID'),
			'customer_id' => Yii::t('customer', 'Player ID'),
			'email_code'  => Yii::t('customer', 'Email Code'),
		];
	}

	/**
	 * @return array
	 */
	public function attributeHints(){
		return [
			'name' => Yii::t('customer',
				'The name must match the name on your verification documents'),
		];
	}

	/**
	 * @return \modules\customer\frontend\models\CustomerIdentity|null
	 */
	public function update(){
		if (!$this->validate()){
			return NULL;
		}

		$user = $this->user;
		$user->detachBehavior('audit');
		$user->phone_number = $this->phone;
		$user->email        = $this->email;

		if (!empty($this->front_image) || !empty($this->back_image)){
			$this->storeKyc();
		}elseif (!empty($user->kyc) && $user->kyc->status == Kyc::STATUS_REJECTED){
			$kyc = $user->kyc;
			$kyc->deleteImage($kyc->front_image);
			$kyc->deleteImage($kyc->back_image);
			$kyc->front_image = NULL;
			$kyc->back_image  = NULL;
			$kyc->save(FALSE);
		}

		return $user->save() ? $user : NULL;
	}

	/**
	 * @return bool
	 */
	public function storeKyc(){
		if (!$this->validate()){
			return NULL;
		}

		$user = Customer::findOne(['id' => Yii::$app->user->id]);
		if (empty($user->kyc)){
			$kyc = new Kyc([
				'customer_id' => $user->id,
			]);
		}else{
			$kyc = $user->kyc;
			$kyc->deleteImage($kyc->front_image);
			$kyc->deleteImage($kyc->back_image);
		}
		$kyc->status = Status::STATUS_INACTIVE;
		$kyc->detachBehavior('audit');

		$front_image_encode = NULL;
		$back_image_encode  = NULL;

		if (!empty($this->front_image)){
			$front_image_encode = base64_encode(file_get_contents($this->front_image->tempName));
		}

		if (!empty($this->back_image)){
			$back_image_encode = base64_encode(file_get_contents($this->back_image->tempName));
		}

		$kyc->front_image = $front_image_encode;
		$kyc->back_image  = $back_image_encode;
		$kyc->storeImages();

		if ($kyc->save()){
			$kyc->mailSubmitNotify();

			return TRUE;
		}

		return FALSE;
	}

	// Verify Email

	/**
	 * @param $attribute
	 *
	 * @return void
	 */
	public function validateEmail($attribute){
		$is_using = Customer::find()
		                    ->andWhere(['email' => $this->$attribute])
		                    ->andWhere(['<>', 'id', Yii::$app->user->id])
		                    ->exists();

		if ($is_using){
			$this->addError($attribute,
				Yii::t('customer', 'This email address has already been taken.'));
		}
	}

	/**
	 * @param $attribute
	 *
	 * @return void
	 */
	public function validateEmailCode($attribute){
		$code = $this->user->verify['email']['code'] ?? NULL;

		if (empty($code) || $code != $this->$attribute){
			$this->addError($attribute,
				Yii::t('customer', 'The verification code is incorrect.'));
		}
	}

	/**
	 * @return bool
	 * @throws \yii\base\Exception
	 */
	public function verify(){
		if ($this->validate()){
			$user = $this->user;

			if (!empty($user->verify['email'])){
				$user->verify = [
					'email' => TRUE
				];

				return $user->save();
			}

			if ($code = Setting::generateCode()){
				$token = Yii::$app->security->generateRandomString() . time();

				$user->email  = $this->email;
				$user->verify = [
					'email' => [
						'code'  => $code,
						'token' => $token
					]
				];

				if ($user->save()){
					return Notification::verifyEmail($user, $code, $token);
				}
			}
		}

		return FALSE;
	}

	private $_user = NULL;

	/**
	 * @return \modules\customer\frontend\models\CustomerIdentity|null
	 */
	public function getUser(){
		if ($this->_user === NULL){
			$this->_user = Yii::$app->user->identity;
		}

		return $this->_user;
	}
}
