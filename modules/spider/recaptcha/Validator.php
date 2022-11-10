<?php

namespace modules\spider\recaptcha;

use Yii;
use yii\base\InvalidConfigException;
use yii\httpclient\Client;
use yii\validators\Validator as BaseValidator;

/**
 * Class ReCaptchaValidator
 *
 * @package common\base
 */
class Validator extends BaseValidator{

	const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

	/** @var \yii\httpclient\Request */
	public $http_client = NULL;
	public $secret_key = NULL;
	public $skipOnEmpty = FALSE;
	public $skipOnError = FALSE;

	/**
	 * @throws \yii\base\InvalidConfigException
	 */
	public function init(){
		parent::init();
		if ($this->message === NULL){
			$this->message = Yii::t('common', 'The captcha verification is expired or incorrect.');
		}

		if ($this->http_client === NULL){
			$this->http_client = (new Client())->createRequest();
		}

		if ($this->secret_key === NULL){
			$this->secret_key = Yii::$app->params['recaptcha']['secret_key'] ?? NULL;

			if (empty($this->secret_key)){
				throw new InvalidConfigException("Required reCAPTCHA key params aren't set.");
			}
		}
	}

	/**
	 * @param \yii\base\Model $model
	 * @param string $attribute
	 *
	 * @throws \yii\httpclient\Exception
	 */
	public function validateAttribute($model, $attribute){
		$value    = $model->$attribute ?? Yii::$app->request->post('g-recaptcha-response');
		$response = $this->getResponse($value);

		if (empty($response['success'])){
			$model->addError($attribute, $this->message);
		}
	}

	/**
	 * @param string $value
	 *
	 * @return array|boolean
	 * @throws \yii\httpclient\Exception
	 */
	protected function getResponse($value){
		$response = $this->http_client
			->setMethod('GET')
			->setUrl(self::VERIFY_URL)
			->setData(['secret' => $this->secret_key, 'response' => $value, 'remoteip' => Yii::$app->request->userIP])
			->send();

		if (!$response->isOk){
			return FALSE;
		}

		return $response->data;
	}
}