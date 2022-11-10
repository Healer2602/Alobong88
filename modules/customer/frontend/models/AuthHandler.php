<?php

namespace modules\customer\frontend\models;

use modules\customer\models\Customer;
use modules\customer\models\CustomerAuth;
use Yii;
use yii\authclient\ClientInterface;
use yii\helpers\ArrayHelper;

/**
 * AuthHandler handles successful authentication via Yii auth component
 */
class AuthHandler{

	/**
	 * @var ClientInterface
	 */
	private $client;

	/**
	 * AuthHandler constructor.
	 *
	 * @param \yii\authclient\ClientInterface $client
	 */
	public function __construct(ClientInterface $client){
		$this->client = $client;
	}

	/**
	 * @throws \yii\base\Exception
	 */
	public function handle(){
		$attributes = $this->client->getUserAttributes();
		$email      = ArrayHelper::getValue($attributes, 'email');
		$id         = ArrayHelper::getValue($attributes, 'id');
		$name       = ArrayHelper::getValue($attributes, 'name', $email);

		$auth = CustomerAuth::findOne([
			'source'    => $this->client->getId(),
			'source_id' => $id
		]);

		// Create Auth record for current user
		if (!empty($auth) && !Yii::$app->user->isGuest){
			$auth = new CustomerAuth([
				'customer_id' => Yii::$app->user->id,
				'source'      => $this->client->getId(),
				'source_id'   => (string) $attributes['id'],
			]);

			return $auth->save();
		}

		if (Yii::$app->user->isGuest){
			if ($auth){
				$customer = CustomerIdentity::findOne([
					'id'     => $auth->customer_id,
					'status' => CustomerIdentity::STATUS_ACTIVE
				]);

				self::return();

				return Yii::$app->user->login($customer, 3600 * 24 * 30);
			}

			if (!Customer::find()->where(['email' => $email])->exists()){
				$transaction = Customer::getDb()->beginTransaction();
				$password    = Yii::$app->security->generateRandomString(8);
				$register    = new RegisterForm([
					'scenario'         => RegisterForm::SCENARIO_SOCIAL,
					'password'         => $password,
					'confirm_password' => $password,
					'email'            => $email,
					'username'         => $email,
					'name'             => $name
				]);

				if ($customer = $register->register()){
					$auth = new CustomerAuth([
						'customer_id' => $customer->id,
						'source'      => $this->client->getId(),
						'source_id'   => (string) $id,
					]);

					if ($auth->save()){
						$transaction->commit();
						self::return();

						return Yii::$app->user->login($customer, 3600 * 24 * 30);
					}
				}

				$transaction->rollBack();
			}else{
				$customer = CustomerIdentity::findOne(['email' => $email]);
				if ($customer){
					$auth = new CustomerAuth([
						'customer_id' => $customer->id,
						'source'      => $this->client->getId(),
						'source_id'   => (string) $attributes['id'],
					]);

					if ($auth->save()){
						self::return();

						return Yii::$app->user->login($customer, 3600 * 24 * 30);
					}
				}
			}
		}

		return FALSE;
	}

	/**
	 * @inheritDoc to init return URL for customer
	 */
	public static function return(){
		$return_url = Yii::$app->user->getReturnUrl();
		if (empty($return_url)){
			Yii::$app->user->setReturnUrl(['/investment/default/packages']);
		}
	}
}