<?php

namespace modules\wallet\frontend\models;

use frontend\base\Rate;
use modules\customer\frontend\models\CustomerIdentity;
use modules\wallet\models\Gateway;
use modules\wallet\models\Setting;
use modules\wallet\models\TopupGateway;
use modules\wallet\models\Transaction;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Class Deposit
 *
 * @package modules\wallet\frontend\models
 *
 * @property-read string $currencyCode
 * @property-read array $gateways
 * @property-read array $gatewayList
 * @property-read array $options
 * @property-read array $channels
 * @property-read TopupGateway $gatewayModel
 * @property-read Model $model
 * @property-read string $help
 * @property-read string $firstOption
 * @property-read int $rate
 */
class Deposit extends Model{

	const SESSION_TOPUP = 'session-topup';

	public $opt;
	public $gateway;
	public $total;
	public $default = 0;
	public $currency;
	public $data = [];

	private $_min_topup = NULL;
	private $_min_topup_first = 0;
	private $_max_topup = 0;

	/**
	 * @inheritDoc
	 */
	public function init(){
		parent::init();

		if ($this->_min_topup === NULL){
			$setting = new Setting();
			$setting->getValues();

			$this->_min_topup       = doubleval($setting->minimum_topup ?? 0);
			$this->_min_topup_first = doubleval($setting->minimum_topup_first ?? 0);
			$this->_max_topup       = doubleval($setting->maximum_topup ?? 0);
		}

		if (empty($this->total)){
			$this->total = !$this->isFirst() ? $this->_min_topup_first : $this->_min_topup;
			if ($this->total < $this->default){
				$this->total = ceil($this->default);
			}
		}

		if (empty($this->opt)){
			$this->opt = Gateway::OPT_BANK_TRANSFER;
		}

		if (empty($this->gateway) && !empty($this->gatewayList)){
			$this->gateway = key($this->gatewayList);
		}
	}

	/**
	 * @return array
	 */
	public function rules(){
		return [
			['total', 'number'],
			[['total', 'gateway'], 'required'],
			['total', 'compare', 'compareValue' => 0, 'operator' => '>'],
			['total', 'validateTotal'],
			[['gateway', 'currency'], 'string'],
			['data', 'safe'],
			['gateway', 'exist', 'targetClass' => TopupGateway::class, 'targetAttribute' => ['gateway' => 'key']]
		];
	}

	/**
	 * @throws \yii\base\InvalidConfigException
	 */
	public function validateTotal(){
		if (!empty($this->total)){
			if (!$this->isFirst()){
				if ($this->total < $this->_min_topup_first){
					$this->addError('total', Yii::t('wallet',
						'The deposit amount for first time must be from {0}', [
							Yii::$app->formatter->asCurrency($this->_min_topup_first)
						]));
				}
			}elseif ($this->total < $this->_min_topup){
				$this->addError('total', Yii::t('wallet',
					'The deposit amount must be from {0}', [
						Yii::$app->formatter->asCurrency($this->_min_topup)
					]));
			}

			if (!Yii::$app->user->identity->isVerified){
				$total = $this->total + $this->getTotalTopup();
				if ($total > $this->_max_topup){
					$this->addError('total', Yii::t('wallet',
						'Your remaining deposit amount is {0} as your account haven\'t verified yet.',
						[
							Yii::$app->formatter->asCurrency($this->_max_topup - $this->getTotalTopup())
						]));
				}
			}
		}
	}

	/**
	 * @return array
	 */
	public function attributeLabels(){
		return [
			'total'    => Yii::t('wallet', 'Deposit amount'),
			'gateway'  => Yii::t('wallet', 'Gateway'),
			'currency' => Yii::t('wallet', 'Currency'),
		];
	}

	private $_is_first = NULL;

	/**
	 * @return bool
	 */
	private function isFirst()
	: bool{
		if ($this->_is_first === NULL){
			$this->_is_first = Transaction::find()
			                              ->andWhere(['type' => Transaction::TYPE_TOPUP])
			                              ->andWhere(['status' => Transaction::STATUS_SUCCESS])
			                              ->andWhere(['wallet_id' => Yii::$app->user->identity->wallet->id ?? NULL])
			                              ->exists();
		}

		return $this->_is_first;
	}


	private $_total_topup = NULL;

	/**
	 * @return double
	 */
	private function getTotalTopup(){
		if ($this->_total_topup === NULL){
			$total = Transaction::find()
			                    ->andWhere(['type' => Transaction::TYPE_TOPUP])
			                    ->andWhere(['status' => Transaction::STATUS_SUCCESS])
			                    ->andWhere(['wallet_id' => Yii::$app->user->identity->wallet->id ?? NULL])
			                    ->sum('amount');

			$this->_total_topup = doubleval($total ?: 0);
		}

		return $this->_total_topup;
	}

	/**
	 * @return string
	 */
	public function getCurrencyCode(){
		return CustomerIdentity::profile()->currency ?? NULL;
	}

	/**
	 * @return array
	 */
	public function getOptions(){
		$options  = Gateway::options();
		$channels = TopupGateway::find()
		                        ->default()
		                        ->andWhere(['option' => array_keys($options)])
		                        ->select(['total' => 'COUNT(*)', 'option'])
		                        ->groupBy(['option'])
		                        ->indexBy('option')
		                        ->column();

		foreach ($options as $key => &$option){
			if (empty($channels[$key])){
				unset($options[$key]);
			}

			$option = [
				'name'  => $option,
				'total' => intval($channels[$key] ?? 0)
			];
		}

		return $options;
	}

	private $_channels = NULL;

	/**
	 * @return array
	 */
	public function getChannels()
	: array{
		if ($this->_channels === NULL){
			$this->_channels = TopupGateway::find()->default()
			                               ->andWhere(['option' => $this->opt])
			                               ->orderBy(['ordering' => SORT_ASC])
			                               ->asArray()
			                               ->all();
		}

		return $this->_channels;
	}

	/**
	 * @return bool|false
	 */
	public function send(){
		if ($this->validate()){
			$model = $this->getModel();

			if (!empty($model) && $model->load($this->data)){
				$model->amount = $this->total * Rate::rate($this->currencyCode);
				if ($model->validate()){
					$customer = CustomerIdentity::profile();
					$wallet   = $customer->wallet;
					$gateway  = $this->getGatewayModel();

					$transaction = new Transaction([
						'wallet_id'  => $wallet->id,
						'balance'    => $wallet->balance,
						'amount'     => $this->total,
						'type'       => Transaction::TYPE_TOPUP,
						'currency'   => $this->currencyCode,
						'gateway_id' => $gateway->id,
						'params'     => [
							'Gateway' => $gateway->name,
						]
					]);

					if ($transaction->save()){
						$model->transaction = $transaction;

						Yii::$app->session->set(self::SESSION_TOPUP, $transaction->id);

						return $model->submit();
					}
				}else{
					$this->addErrors($model->errors);
				}
			}
		}

		return FALSE;
	}

	private $_gateways = NULL;

	/**
	 * @return array
	 */
	public function getGateways(){
		if ($this->_gateways === NULL){
			$this->_gateways = TopupGateway::find()
			                               ->select(['gateway.title', 'key'])
			                               ->default()
			                               ->joinWith('customerRankMaps', 'false')
			                               ->andWhere(['OR', ['customer_rank_id' => NULL], ['customer_rank_id' => Yii::$app->user->identity->rank->id ?? NULL]])
			                               ->andWhere(['option' => $this->opt])
			                               ->orderBy(['ordering' => SORT_ASC])
			                               ->indexBy('key')
			                               ->column();
		}

		return $this->_gateways;
	}

	/**
	 * @return array
	 */
	public function getGatewayList(){
		if (empty($this->gateways)){
			return [];
		}

		return ArrayHelper::getColumn($this->gateways, 'name');
	}

	private $_gateway_model = NULL;

	/**
	 * @return \modules\wallet\models\TopupGateway|null
	 */
	public function getGatewayModel(){
		if ($this->_gateway_model === NULL){
			$this->_gateway_model = TopupGateway::findOne(['key' => $this->gateway]) ?? [];
		}

		return $this->_gateway_model;
	}

	/**
	 * @return \modules\wallet\gateways\DepositAbstract|null
	 */
	public function getModel(){
		if (!empty($this->gatewayModel->formModel)){
			/**@var \modules\wallet\gateways\DepositAbstract $model */
			$model = new $this->gatewayModel->formModel;
			$model->load($this->data);
			if (!empty($this->errors)){
				$model->addErrors($this->errors);
			}

			return $model;
		}

		return NULL;
	}

	/**
	 * @return string
	 */
	public function getHelp(){
		return Yii::t('wallet', 'Min/Max Limit: {0}/{1}', [
			Yii::$app->formatter->asDecimal($this->_min_topup),
			Yii::$app->formatter->asDecimal($this->_max_topup),
		]);
	}

	/**
	 * @return int
	 */
	public function getRate(){
		return Rate::rate($this->currencyCode);
	}

	/**
	 * @return int|string|null
	 */
	public function getFirstOption(){
		$options = array_filter($this->options, function ($value){
			return !empty($value['total']);
		});

		if ($options){
			return array_key_first($options);
		}

		return NULL;
	}
}