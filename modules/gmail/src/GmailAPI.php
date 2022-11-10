<?php

namespace modules\gmail\src;

use Exception;
use Google\Auth\HttpHandler\HttpHandlerFactory;
use Google\Auth\OAuth2;
use Google\Service\Gmail;
use Google_Client;
use GuzzleHttp\Client;
use InvalidArgumentException;
use modules\gmail\models\GoogleSetting;
use Yii;
use yii\base\BaseObject;
use yii\helpers\Json;
use function strlen;
use function time;

/**
 * Gmail API
 */
class GmailAPI extends BaseObject{

	private $_options = NULL;

	/**
	 * @return void
	 */
	public function init(){
		parent::init();

		if ($this->_options === NULL){
			$options = new GoogleSetting();
			$options->getValues();

			$this->_options = $options;
		}
	}

	/**
	 * @return \Google_Client
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\db\Exception
	 */
	public function getClient(){
		$client = new Google_Client([
			'client_id'     => $this->_options['client_id'],
			'client_secret' => $this->_options['client_secret'],
			'redirect_uris' => [
				self::redirectURI()
			],
		]);

		$client->setApplicationName(Yii::$app->name);
		$client->setAccessType('offline');
		$client->setApprovalPrompt('force');
		$client->setIncludeGrantedScopes(TRUE);
		$client->setScopes([Gmail::MAIL_GOOGLE_COM]);
		$client->setRedirectUri(self::redirectURI());
		$client->setState(self::authURI());

		if ($this->isAuthRequired() && !empty($this->_options['auth_code'])){
			try{
				$creds = $client->fetchAccessTokenWithAuthCode($this->_options['auth_code']);
			}catch (Exception $exception){
				$creds['error'] = $exception->getMessage();
			}

			// Bail if we have an error.
			if (!empty($creds['error'])){
				if ($creds['error'] === 'invalid_client'){
					$creds['error'] .= Yii::t('common',
						'Please make sure your Google Client ID and Secret in the plugin settings are valid. Save the settings and try the Authorization again.');
				}

				return $client;
			}

			GoogleSetting::store('access_token', $client->getAccessToken());
			GoogleSetting::store('refresh_token', $client->getRefreshToken());
		}

		if (!empty($this->_options['access_token'])){
			$client->setAccessToken($this->_options['access_token']);
		}

		// Refresh the token if it's expired .
		if ($client->isAccessTokenExpired()){
			$refresh = $client->getRefreshToken();
			if (empty($refresh) && isset($this->_options['refresh_token'])){
				$refresh = $this->_options['refresh_token'];
			}

			if (!empty($refresh)){
				try{
					$creds = $client->fetchAccessTokenWithRefreshToken($refresh);
				}catch (Exception $exception){
					$creds['error'] = $exception->getMessage();
				}

				// Bail if we have an error.
				if (!empty($creds['error'])){
					return $client;
				}

				GoogleSetting::store('access_token', $client->getAccessToken());
				GoogleSetting::store('refresh_token', $client->getRefreshToken());
			}
		}

		return $client;
	}

	/**
	 * Attempt to exchange a code for an valid authentication token.
	 * Helper wrapped around the OAuth 2.0 implementation.
	 *
	 * @param string $code code from accounts.google.com
	 *
	 * @return array access token
	 * @throws \Exception
	 */
	public function fetchAccessTokenWithAuthCode($code){
		if (strlen($code) == 0){
			throw new InvalidArgumentException("Invalid code");
		}

		$auth = $this->getOAuth2Service();
		$auth->setCode($code);
		$auth->setRedirectUri(self::redirectURI());

		$http_handler = HttpHandlerFactory::build($this->getHttpClient());
		$creds        = $auth->fetchAuthToken($http_handler);
		if ($creds && isset($creds['access_token'])){
			$creds['created'] = time();
			GoogleSetting::store('access_token', Json::encode($creds));
			GoogleSetting::store('refresh_token', $creds['refresh_token'] ?? NULL);
		}

		return $creds;
	}

	private $_auth = NULL;

	/**
	 * create a default google auth object
	 */
	protected function getOAuth2Service(){
		if ($this->_auth === NULL){
			$this->_auth = new OAuth2([
				'clientId'           => $this->_options['client_id'],
				'clientSecret'       => $this->_options['client_secret'],
				'authorizationUri'   => \Google\Client::OAUTH2_AUTH_URL,
				'tokenCredentialUri' => \Google\Client::OAUTH2_TOKEN_URI,
				'redirectUri'        => self::redirectURI(),
				'issuer'             => $this->_options['client_id'],
				'signingKey'         => NULL,
				'signingAlgorithm'   => NULL
			]);
		}

		return $this->_auth;
	}

	private $_http_client = NULL;

	/**
	 * @return \GuzzleHttp\Client
	 */
	protected function getHttpClient(){
		if ($this->_http_client === NULL){
			$this->_http_client = new Client([
				'base_uri'    => \Google\Client::API_BASE_PATH,
				'http_errors' => FALSE,
				'verify'      => FALSE
			]);
		}

		return $this->_http_client;
	}

	/**
	 * @return bool
	 */
	private function isAuthRequired(){
		return empty($this->_options['access_token']) || empty($this->_options['refresh_token']);
	}

	/**
	 * @return string
	 */
	public static function redirectURI(){
		return Yii::$app->urlManager->createAbsoluteUrl(['/gmail/setting/auth'], 'https');
	}

	/**
	 * @return string
	 */
	public static function authURI(){
		return Yii::$app->urlManager->createAbsoluteUrl(['/gmail/setting/index'], 'https');
	}
}