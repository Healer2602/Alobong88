<?php

namespace modules\wallet\frontend\models;

use modules\customer\frontend\models\CustomerIdentity;
use modules\wallet\models\Gateway;
use modules\wallet\models\Setting;
use modules\wallet\models\Transaction;
use modules\wallet\models\WithdrawGateway;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Class Withdraw
 *
 * @package modules\wallet\frontend\models
 *
 * @property-read double $fee
 * @property-read string $currencyCode
 * @property-read string $feeAmount
 * @property-read \modules\wallet\models\Wallet $wallet
 * @property-read array $gateways
 * @property-read array $gatewayList
 * @property-read array $currencies
 * @property-read double $amount
 * @property-read array $externalGateways
 * @property-read array $options
 * @property-read array $channels
 * @property-read WithdrawGateway $gatewayModel
 * @property-read Model $model
 * @property-read string $firstOption
 * @property-read string $turnoverMessage
 * @property-read boolean $needKyc
 */
class Withdraw extends Model{

	const SESSION_WITHDRAW = 'session_withdraw';

	public $opt;
	public $total;
	public $gateway;
	public $currency;
	public $data = [];
	public $max_withdraw_wo_kyc = 0;
	public $reach = 0;
	public $rank;

	private $_min_withdraw = NULL;
	private $_max_withdraw = NULL;
	private $_max_withdraw_approval = NULL;
	private $_daily_limit_balance = 0;
	private $_daily_count_balance = 0;
	private $_need_turnover = FALSE;

	/**
	 * @inheritDoc
	 */
	public function init(){
		parent::init();

		if ($this->_max_withdraw === NULL){
			if ($this->wallet->turnover < $this->wallet->total_deposit){
				$this->_max_withdraw  = 0;
				$this->_need_turnover = TRUE;
			}else{
				$this->_max_withdraw = round($this->wallet->turnover ?? 0, 2);
				if (!empty($this->wallet->balance) && $this->_max_withdraw > $this->wallet->balance){
					$this->_max_withdraw = round($this->wallet->balance ?? 0, 2);
				}

				if ($this->_max_withdraw < 0){
					$this->_max_withdraw = 0;
				}
			}
		}

		if ($this->_min_withdraw === NULL){
			$setting = new Setting();
			$setting->getValues();

			$this->_min_withdraw          = $setting->minimum_withdraw ?? 0;
			$this->_max_withdraw_approval = $setting->maximum_withdraw ?? 0;

			$this->_daily_count_balance = !empty($this->rank['daily_count_balance']) ? $this->rank['daily_count_balance'] : $setting->daily_count_balance;
			$this->_daily_limit_balance = !empty($this->rank['daily_limit_balance']) ? $this->rank['daily_limit_balance'] : $setting->daily_limit_balance;

			$max_withdraw = !empty($this->rank['withdraw_limit_balance']) ? $this->rank['withdraw_limit_balance'] : $setting->withdraw_limit_balance;

			$this->_max_withdraw       = min($this->_max_withdraw, $max_withdraw);
			$this->max_withdraw_wo_kyc = min($this->_max_withdraw,
				$setting->maximum_withdraw_wo_kyc ?? 0);

			if ($this->needKyc){
				$this->_max_withdraw = min($this->_max_withdraw, $this->max_withdraw_wo_kyc);
			}
		}

		if (empty($this->total)){
			$this->total = min($this->_min_withdraw, $this->_max_withdraw);
		}

		if (empty($this->opt)){
			$this->opt = Gateway::OPT_BANK_TRANSFER;
		}

		if (!empty($this->gatewayList) && empty($this->gateway)){
			$this->gateway = key($this->gatewayList);
		}
	}

	/**
	 * @return array
	 */
	public function rules(){
		return [
			[['total', 'gateway'], 'required'],
			[['gateway', 'currency'], 'string'],
			['total', 'number'],
			['total', 'compare',
				'compareValue' => $this->_min_withdraw,
				'operator'     => $this->_min_withdraw ? '>=' : '>',
				'message'      => Yii::t('wallet',
					'Withdraw amount must be greater than or equal to {0}.',
					Yii::$app->formatter->asDecimal($this->_min_withdraw))
			],
			['total', 'compare',
				'compareValue' => $this->_max_withdraw,
				'operator'     => '<=',
				'message'      => Yii::t('wallet',
					'Withdraw amount must be less than or equal to {0}.',
					Yii::$app->formatter->asDecimal($this->_max_withdraw))
			],
			['total', 'validateBalance']
		];
	}

	/**
	 * @param $attribute
	 *
	 * @return void
	 */
	public function validateBalance($attribute){
		if ($this->_daily_count_balance - $this->countBalance() <= 0){
			$this->addError($attribute,
				Yii::t('wallet', 'You have reached your limit of withdrawal.'));
		}elseif ($this->_daily_limit_balance - $this->dailyBalance() < $this->total){
			$this->addError($attribute,
				Yii::t('wallet', 'Withdraw amount must be less than or equal to {0}.',
					Yii::$app->formatter->asDecimal($this->_daily_limit_balance - $this->dailyBalance())));
		}
	}

	/**
	 * @return array
	 */
	public function attributeLabels(){
		return [
			'total'    => Yii::t('wallet', 'Amount'),
			'currency' => Yii::t('wallet', 'Currency'),
			'gateway'  => Yii::t('wallet', 'Gateway'),
		];
	}

	/**
	 * @return double
	 */
	public function getFee(){
		return 0.01 * ($this->gateways[$this->gateway]['fee'] ?? 0);
	}

	/**
	 * @return string
	 */
	public function getFeeAmount(){
		return Yii::$app->formatter->asDecimal($this->total * $this->fee);
	}

	/**
	 * @return string
	 */
	public function getCurrencyCode(){
		return CustomerIdentity::profile()->currency ?? NULL;
	}

	/**
	 * @return \modules\wallet\models\Wallet|null
	 */
	public function getWallet(){
		return CustomerIdentity::profile()->wallet ?? NULL;
	}

	/**
	 * @return bool
	 */
	public function canWithdraw(){
		return $this->hasInvested() && !empty($this->gatewayList) && $this->_max_withdraw >= $this->_min_withdraw && $this->wallet->isActive() && $this->wallet->verify();
	}

	/**
	 * @return bool
	 */
	public function isEmptyWallet(){
		return empty($this->wallet->balance);
	}

	/**
	 * @return bool
	 */
	public function isNotEnough(){
		return $this->_max_withdraw < $this->_min_withdraw;
	}

	/**
	 * @return null
	 */
	public function getMinWithdraw(){
		return $this->_min_withdraw;
	}

	private $_gateways = NULL;

	/**
	 * @return array
	 */
	public function getGateways(){
		if ($this->_gateways === NULL){
			$gateways = WithdrawGateway::find()
			                           ->default()
			                           ->joinWith('customerRankMaps', 'false')
			                           ->andWhere(['OR', ['customer_rank_id' => NULL], ['customer_rank_id' => Yii::$app->user->identity->rank->id ?? NULL]])
			                           ->andWhere(['option' => $this->opt])
			                           ->orderBy(['ordering' => SORT_ASC])
			                           ->all();
			$results  = [];
			if (!empty($gateways)){
				foreach ($gateways as $gateway){
					$results[$gateway->key] = [
						'name'     => $gateway->name,
						'external' => $gateway->gateway->config['external_withdraw'] ?? FALSE,
						'fee'      => $gateway->fee
					];
					if (!empty($gateway->currencies)){
						$results[$gateway->key]['currencies'] = $gateway->currencies;
					}
				}
			}

			$this->_gateways = $results;
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

	/**
	 * @return array
	 */
	public function getCurrencies(){
		return array_combine(array_keys($this->listCurrencies), array_keys($this->listCurrencies));
	}

	/**
	 * @return false
	 */
	public function isExternal(){
		return $this->gateways[$this->gateway]['external'] ?? FALSE;
	}

	/**
	 * @return boolean
	 */
	public function send(){
		if ($this->hasInvested() && $this->validate()){
			$model = $this->getModel();
			if (!empty($model) && $model->load($this->data)){
				$model->amount = abs($this->total);
				if ($model->validate()){
					$wallet  = $this->wallet;
					$gateway = $this->getGatewayModel();

					$transaction = new Transaction([
						'wallet_id'  => $this->wallet->id,
						'balance'    => $wallet->balance,
						'amount'     => $this->total,
						'status'     => Transaction::STATUS_PENDING,
						'type'       => Transaction::TYPE_WITHDRAW,
						'gateway_id' => $gateway->id,
						'currency'   => $this->currency ?? $this->currencyCode,
						'params'     => [
							'Gateway'      => $gateway->name,
							$this->gateway => $model->getAttributes(NULL, ['transaction'])
						]
					]);

					if ($transaction->save()){
						$wallet->balance -= $this->total;
						if ($wallet->save(FALSE)){
							if ($this->total <= $this->_max_withdraw_approval){
								Yii::$app->session->set(self::SESSION_WITHDRAW, $transaction->id);
								$model->transaction = $transaction;

								return $model->submit();
							}

							return TRUE;
						}
					}
				}
			}
		}

		return FALSE;
	}

	private $_has_invested = NULL;

	/**
	 * @return bool
	 */
	public function hasInvested(){
		if ($this->_has_invested === NULL){
			$this->_has_invested = !empty($this->wallet->turnover) && $this->wallet->turnover > 0;
		}

		return $this->_has_invested;
	}

	private $_list_currencies = NULL;

	/**
	 * @return array
	 */
	public function getListCurrencies(){
		if ($this->_list_currencies === NULL){
			$model = new WithdrawGateway([
				'type' => WithdrawGateway::TYPE_WITHDRAW
			]);

			$list_currencies    = $model->listCurrencies;
			$country_currencies = $this->getCountryCurrencies();
			if (empty($country_currencies)){
				$list_currencies = [];
			}else{
				$list_currencies = array_intersect_key($list_currencies, $country_currencies);
			}

			$this->_list_currencies = $list_currencies;
		}

		return $this->_list_currencies;
	}

	/**
	 * @return array
	 */
	public function getCountryCurrencies(){
		/**@var \modules\customer\models\Customer $customer */
		$customer = Yii::$app->user->identity;
		$country  = $customer->country_code;
		$gateway  = WalletCountry::find()
		                         ->default()
		                         ->andWhere(['country_code' => $country, 'type' => WalletCountry::TYPE_WITHDRAW])
		                         ->one();

		if (empty($gateway)){
			$gateway = WalletCountry::find()
			                        ->default()
			                        ->andWhere(['country_code' => WalletCountry::COUNTRY_ALL, 'type' => WalletCountry::TYPE_WITHDRAW])
			                        ->one();
		}

		if (empty($gateway)){
			return [];
		}

		return array_combine($gateway->currencies, $gateway->currencies);
	}

	/**
	 * @return array
	 */
	public function getOptions(){
		$options  = Gateway::options();
		$channels = WithdrawGateway::find()
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
			$this->_channels = WithdrawGateway::find()->default()
			                                  ->andWhere(['option' => $this->opt])
			                                  ->orderBy(['ordering' => SORT_ASC])
			                                  ->asArray()
			                                  ->all();
		}

		return $this->_channels;
	}

	private $_gateway_model = NULL;

	/**
	 * @return \modules\wallet\models\WithdrawGateway|null
	 */
	public function getGatewayModel(){
		if ($this->_gateway_model === NULL){
			$this->_gateway_model = WithdrawGateway::findOne(['key' => $this->gateway]) ?? [];
		}

		return $this->_gateway_model;
	}

	/**
	 * @return \modules\wallet\gateways\WithdrawAbstract|null
	 */
	public function getModel(){
		if (!empty($this->gatewayModel->formModel)){
			/**@var \modules\wallet\gateways\WithdrawAbstract $model */
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
	 * @return int|string|void|null
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

	private $_daily_balance = NULL;

	/**
	 * @return bool|int|mixed|string|null
	 */
	private function dailyBalance(){
		if ($this->_daily_balance === NULL){
			$from = strtotime('today midnight');

			$this->_daily_balance = Transaction::find()
			                                   ->andWhere(['type' => Transaction::TYPE_WITHDRAW])
			                                   ->andWhere(['status' => [Transaction::STATUS_PENDING, Transaction::STATUS_SUCCESS]])
			                                   ->andWhere(['BETWEEN', 'created_at', $from, $from + 86400 - 1])
			                                   ->andWhere(['wallet_id' => $this->wallet->id ?? NULL])
			                                   ->sum('amount');
		}

		return $this->_daily_balance;
	}

	private $_count_balance = NULL;

	/**
	 * @return bool|int|string|null
	 */
	private function countBalance(){
		if ($this->_count_balance === NULL){
			$from = strtotime('today midnight');

			$this->_count_balance = Transaction::find()
			                                   ->andWhere(['type' => Transaction::TYPE_WITHDRAW])
			                                   ->andWhere(['status' => [Transaction::STATUS_PENDING, Transaction::STATUS_SUCCESS]])
			                                   ->andWhere(['BETWEEN', 'created_at', $from, $from + 86400 - 1])
			                                   ->andWhere(['wallet_id' => $this->wallet->id ?? NULL])
			                                   ->count();
		}

		return $this->_count_balance;
	}

	/**
	 * @return string
	 * @throws \yii\base\InvalidConfigException
	 */
	public function getHelp(){
		return Yii::t('wallet', 'Daily Limit Balance: {0} / Daily Count Balance: {1}', [
			Yii::$app->formatter->asCurrency($this->_daily_limit_balance - $this->dailyBalance()),
			Yii::$app->formatter->asInteger($this->_daily_count_balance - $this->countBalance()),
		]);
	}

	/**
	 * @return bool
	 */
	public function getNeedKyc(){
		return !(CustomerIdentity::profile()->isVerified ?? FALSE);
	}

	/**
	 * @return string
	 */
	public function getTurnoverMessage(){
		if ($this->_need_turnover){
			return Yii::t('wallet', 'You must reach {0} of bet turnover to be able to withdraw', [
				$this->currencyCode . ' ' . Yii::$app->formatter->asDecimal($this->wallet->total_deposit),
			]);
		}

		return '';
	}
}