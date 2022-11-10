<?php

namespace modules\gmail\models;

use common\models\SettingForm;
use Exception;
use Google\Service\Gmail;
use modules\gmail\src\GmailAPI;
use Yii;

/**
 * Google Setting
 *
 * @property-read string $redirectUri
 * @property-read array $connection
 */
class GoogleSetting extends SettingForm{

	const ACTION_REMOVE = 2;

	const ACTION_CONNECT = 1;

	public $client_id;
	public $client_secret;
	public $auth_code;
	public $access_token;
	public $refresh_token;
	public $email_sender;
	public $email_sender_name;

	/**
	 * @return array[]
	 */
	public function rules(){
		return [
			[['client_id', 'client_secret', 'auth_code', 'access_token', 'refresh_token', 'email_sender_name'], 'string'],
			[['client_id', 'client_secret', 'email_sender', 'email_sender_name'], 'required'],
			[['email_sender'], 'email'],
		];
	}

	/**
	 * @return array
	 */
	public function attributeLabels(){
		return [
			'client_id'         => Yii::t('gmail', 'Client ID'),
			'client_secret'     => Yii::t('gmail', 'Client Secret'),
			'email_sender'      => Yii::t('gmail', 'From Email'),
			'email_sender_name' => Yii::t('gmail', 'From Name'),
			'redirectUri'       => Yii::t('gmail', 'Authorized Redirect URI'),
		];
	}

	/**
	 * @return string
	 */
	public function getRedirectUri(){
		return GmailAPI::redirectURI();
	}

	/**
	 * @return false|void
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\db\Exception
	 */
	public function connect(){
		if (!$this->hasAuthorization() && ($client = $this->getClient())){
			$auth_url = $client->createAuthUrl();
			if (!empty($auth_url)){
				Yii::$app->response->redirect($auth_url)->send();
				exit();
			}
		}

		return FALSE;
	}

	/**
	 * @return false|int
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\db\Exception
	 */
	public function remove(){
		if ($this->hasAuthorization()){
			$client = $this->getClient();
			if ($client->revokeToken()){
				return self::store('access_token', NULL);
			}
		}

		return FALSE;
	}

	/**
	 * @param $code
	 *
	 * @return array|false
	 * @throws \Exception
	 */
	public function setToken($code){
		if (!$this->hasAuthorization()){
			$api = new GmailAPI();

			return $api->fetchAccessTokenWithAuthCode($code);
		}

		return FALSE;
	}

	/**
	 * @param $field
	 * @param $value
	 *
	 * @return false|int
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\db\Exception
	 */
	public static function store($field, $value){
		$model = new static();
		$model->getValues();

		if ($model->hasProperty($field)){
			$model->$field = $value;
		}

		return $model->save();
	}

	/**
	 * @return array|bool
	 */
	public function hasAuthorization(){
		if (empty($this->client_secret) || empty($this->client_id)){
			return [
				'error' => Yii::t('gmail',
					'You need to save settings with Client ID and Client Secret before you can proceed.')
			];
		}

		if (empty($this->access_token) || empty($this->refresh_token)){
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * @return string[]
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\db\Exception
	 */
	public function getConnection(){
		$gmail = new Gmail($this->getClient());

		try{
			$email = $gmail->users->getProfile('me')->getEmailAddress();
		}catch (Exception $exception){
			$email = '';
		}

		return ['email' => $email];
	}

	private $_client = NULL;

	/**
	 * @return array|\Google_Client
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\db\Exception
	 */
	protected function getClient(){
		if ($this->_client === NULL){
			$api           = new GmailAPI();
			$this->_client = $api->getClient() ?: [];
		}

		return $this->_client;
	}
}