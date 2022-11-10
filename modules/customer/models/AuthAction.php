<?php

namespace modules\customer\models;

use Yii;
use yii\authclient\AuthAction as BaseAuthAction;
use yii\web\NotFoundHttpException;

/**
 * Class AuthAction
 *
 * @package modules\customer\models
 */
class AuthAction extends BaseAuthAction{

	/**
	 * Runs the action.
	 */
	public function run(){
		$clientId = Yii::$app->getRequest()->getQueryParam($this->clientIdGetParamName);
		if (!empty($clientId)){
			/* @var $collection \yii\authclient\Collection */
			$collection = Yii::$app->get($this->clientCollection);
			if (!$collection->hasClient($clientId)){
				throw new NotFoundHttpException("Unknown auth client '{$clientId}'");
			}
			/* @var $client \yii\authclient\OAuth2 */
			$client  = $collection->getClient($clientId);
			$setting = new SocialLoginSetting();
			$setting->getValues();
			$client_name = $clientId . '_client';
			$secret_name = $clientId . '_secret';
			if (!empty($setting->{$client_name}) && !empty($setting->{$secret_name})){
				$client->clientId     = $setting->{$client_name};
				$client->clientSecret = $setting->{$secret_name};
			}

			return $this->auth($client);
		}

		throw new NotFoundHttpException();
	}
}