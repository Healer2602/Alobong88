<?php

namespace modules\wallet\models;

use common\base\Status;
use common\models\BaseActiveRecord;
use modules\customer\models\CustomerRank;
use Symfony\Component\Yaml\Yaml;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%wallet_withdraw_gateway}}".
 *
 * @property int $id
 * @property string $title
 * @property string $key
 * @property int $type
 * @property string|array $currency
 * @property double $fee
 * @property string $icon
 * @property string $option
 * @property string $endpoint
 * @property string $api_key
 * @property string $api_secret
 * @property int $is_sandbox
 * @property int $created_at
 * @property int $created_by
 * @property int $updated_at
 * @property int $updated_by
 * @property int $status
 * @property int $ordering
 *
 * @property-read \modules\wallet\gateways\GatewayAbstract $gateway
 * @property-read string $name
 * @property-read string $optionName
 * @property-read array $options
 * @property-read array $currencies
 * @property-read array $gatewayName
 * @property-read array $customerRankList
 * @property-read array|string $rankNames
 * @property-read CustomerRank[] $customerRanks
 * @property-read \modules\wallet\models\GatewayCustomerRank[] $customerRankMaps
 * @property-read array $listCurrencies
 * @property-read string $formPath
 * @property-read string $formModel
 */
class Gateway extends BaseActiveRecord{

	const TYPE_WITHDRAW = 1;

	const TYPE_TOPUP = 2;

	const OPT_BANK_TRANSFER = 'bank_transfer';

	const OPT_QUICK_PAY = 'quick_pay';

	const OPT_USDT = 'usdt';

	const OPT_BUSD = 'busd';

	public static $alias = 'gateway';

	public $ranks = [];

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%wallet_gateway}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['key', 'api_key', 'api_secret', 'type', 'title', 'fee', 'option'], 'required'],
			[['is_sandbox', 'created_at', 'created_by', 'updated_at', 'updated_by', 'status', 'type'], 'integer'],
			[['key', 'api_key', 'api_secret', 'title'], 'string', 'max' => 255],
			[['icon', 'option'], 'string'],
			['currency', 'safe'],
			['key', 'unique', 'targetAttribute' => ['key', 'type']],
			['ranks', 'safe'],
			['fee', 'double', 'min' => 0, 'max' => 100],
			['ordering', 'default', 'value' => 1],
			['endpoint', 'url']
		];
	}

	/**
	 * @return array
	 */
	public function behaviors(){
		$behaviors = parent::behaviors();
		unset($behaviors['language']);

		return $behaviors;
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'           => Yii::t('wallet', 'ID'),
			'title'        => Yii::t('common', 'Name'),
			'key'          => Yii::t('wallet', 'Gateway'),
			'type'         => Yii::t('wallet', 'Type'),
			'currency'     => Yii::t('wallet', 'Currency'),
			'fee'          => Yii::t('wallet', 'Fee'),
			'endpoint'     => Yii::t('wallet', 'Endpoint'),
			'icon'         => Yii::t('wallet', 'Icon'),
			'option'       => Yii::t('wallet', 'Option'),
			'deposit_fee'  => Yii::t('wallet', 'Deposit fee'),
			'withdraw_fee' => Yii::t('wallet', 'Withdraw fee'),
			'is_sandbox'   => Yii::t('wallet', 'Sandbox Mode'),
			'api_key'      => Yii::t('wallet', 'Public Key'),
			'api_secret'   => Yii::t('wallet', 'Secret Key'),
			'created_at'   => Yii::t('common', 'Created at'),
			'created_by'   => Yii::t('common', 'Created By'),
			'updated_at'   => Yii::t('common', 'Updated At'),
			'updated_by'   => Yii::t('common', 'Updated By'),
			'status'       => Yii::t('common', 'Status'),
			'ranks'        => Yii::t('customer', 'Player Rank'),
			'ordering'     => Yii::t('customer', 'Ordering'),
		];
	}

	/**
	 * @return array
	 */
	public function attributeHints(){
		return [
			'api_key'    => Yii::t('wallet', 'Merchant ID, API Key or Client ID,...'),
			'api_secret' => Yii::t('wallet', 'IPN Key, Private Key or API Secret key,...'),
			'ranks'      => Yii::t('wallet', 'Leave it blank to enable for all customer ranks'),
		];
	}

	/**
	 * @param bool $insert
	 *
	 * @return bool
	 */
	public function beforeSave($insert){
		if (empty($this->currency)){
			$this->currency = [];
		}

		if (is_array($this->currency)){
			$this->currency = Json::encode($this->currency);
		}

		return parent::beforeSave($insert);
	}

	/**
	 * @inheritDoc
	 */
	public function afterFind(){
		if (is_string($this->currency)){
			$this->currency = Json::decode($this->currency);
		}

		parent::afterFind();
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getCustomerRankMaps(){
		return $this->hasMany(GatewayCustomerRank::class, ['wallet_gateway_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 * @throws \yii\base\InvalidConfigException
	 */
	public function getCustomerRanks(){
		return $this->hasMany(CustomerRank::class, ['id' => 'customer_rank_id'])
		            ->viaTable(GatewayCustomerRank::tableName(), ['wallet_gateway_id' => 'id']);
	}

	/**
	 * @return array
	 * @throws \Throwable
	 */
	public function getCustomerRankList(){
		return CustomerRank::findList();
	}

	/**
	 * @return mixed|null
	 */
	public function getOptionName(){
		return self::options()[$this->option] ?? NULL;
	}

	/**
	 * @param $source
	 *
	 * @return array
	 */
	public static function loadGatewayConfig($source = NULL){
		if (empty($source)){
			$path = '*';
		}else{
			$path = $source;
		}

		$pattern = Yii::getAlias("@modules/{$path}/gateway.yml");
		$files   = glob($pattern) ?: [];
		$sources = [];
		foreach ($files as $file){
			$config                 = Yaml::parseFile($file);
			$sources[$config['id']] = $config;
		}

		if ($source && $sources){
			return $sources[$source];
		}

		return $sources;
	}

	/**
	 * @return array
	 */
	public static function findGateways(){
		return ArrayHelper::map(self::loadGatewayConfig(), 'id', 'name');
	}

	/**
	 * @return array
	 */
	public static function supportCurrencies(){
		$currencies = ArrayHelper::getColumn(self::loadGatewayConfig(), 'currency', FALSE);
		$currencies = array_unique(array_filter(array_merge(...$currencies)));

		return ArrayHelper::map($currencies, function ($data){
			return $data;
		}, function ($data){
			return $data;
		});
	}

	/**
	 * @return \modules\wallet\gateways\GatewayAbstract|null
	 */
	public function getGateway(){
		if ($this->key){
			$source_config = self::loadGatewayConfig($this->key);
			$model         = $source_config['model'];
			$source_config = ArrayHelper::merge($source_config,
				$this->getAttributes(['api_key', 'is_sandbox', 'api_secret', 'endpoint']));

			return new $model([
				'config' => $source_config
			]);
		}

		return NULL;
	}

	/**
	 * @return mixed|string
	 */
	public function getGatewayName(){
		return $this->gateway->config['name'] ?? $this->key;
	}

	/**
	 * @return mixed|string
	 */
	public function getName(){
		if (!empty($this->title)){
			return $this->title;
		}

		return $this->gateway->config['name'] ?? $this->key;
	}

	/**
	 * @return array
	 */
	public function getNewGateways(){
		$gateways         = self::findGateways();
		$current_gateways = static::find()->select('key')->indexBy('key')->column();
		if (!empty($this->key)){
			unset($current_gateways[$this->key]);
		}

		return array_diff_key($gateways, $current_gateways);
	}

	/**
	 * @return array
	 */
	public static function statuses(){
		$statuses = Status::states();
		unset($statuses[Status::STATUS_ALL]);

		return $statuses;
	}

	public $rates = NULL;

	/**
	 * @return array|null
	 */
	public function getCurrencies(){
		if (!empty($this->gateway->config['norate'])){
			return ArrayHelper::map($this->currency, function ($data){
				return $data;
			}, function ($data){
				return $data;
			});
		}

		if ($this->rates === NULL){
			$this->rates = Rate::find()
			                   ->default()
			                   ->select(['rate_currency_code'])
			                   ->indexBy('rate_currency_code')
			                   ->column();
		}

		$currencies = array_intersect($this->rates, $this->currency);
		if (ArrayHelper::isIn('USDT', $this->currency)){
			$currencies['USDT'] = 'USDT';
		}

		if (!empty($this->currency['USD']) || ArrayHelper::isIn('USD', $this->currency)){
			$currencies['USD'] = 'USD';
		}

		if (!empty($this->currency['LTCT']) || ArrayHelper::isIn('LTCT', $this->currency)){
			$currencies['LTCT'] = 'LTCT';
		}

		return $currencies;
	}

	/**
	 * @return bool|int
	 * @throws \yii\db\Exception
	 */
	public function storeRanks(){
		GatewayCustomerRank::deleteAll(['wallet_gateway_id' => $this->id]);
		if (!empty($this->ranks)){
			$data = ArrayHelper::getColumn($this->ranks, function ($data){
				return [
					'wallet_gateway_id' => $this->id,
					'customer_rank_id'  => $data
				];
			});

			return Yii::$app->db->createCommand()
			                    ->batchInsert(GatewayCustomerRank::tableName(),
				                    ['wallet_gateway_id', 'customer_rank_id'], $data)
			                    ->execute();
		}

		return TRUE;
	}

	/**
	 * @return array|string
	 */
	public function getRankNames(){
		if (empty($this->customerRanks)){
			return Yii::t('common', 'All');
		}

		return ArrayHelper::getColumn($this->customerRanks, 'name');
	}

	/**
	 * @return array
	 */
	public function getListCurrencies(){
		$data = self::find()
		            ->select(['currency', 'key'])
		            ->andWhere(['type' => $this->type])
		            ->andWhere(['status' => Status::STATUS_ACTIVE])
		            ->indexBy('key')
		            ->column();

		$items = [];
		foreach ($data as $key => $datum){
			$currency = Json::decode($datum);
			foreach ($currency as $item){
				$items[$item][] = $key;
			}
		}
		ksort($items);

		return $items;
	}

	/**
	 * @return array
	 */
	public static function options()
	: array{
		return [
			self::OPT_BANK_TRANSFER => Yii::t('common', 'Bank Transfer'),
			self::OPT_QUICK_PAY     => Yii::t('common', 'Quick Pay'),
			self::OPT_USDT          => Yii::t('common', 'USDT'),
			self::OPT_BUSD          => Yii::t('common', 'BUSD'),
		];
	}

	/**
	 * @return array
	 */
	public function getOptions()
	: array{
		return self::options();
	}

	/**
	 * @param $option
	 *
	 * @return array
	 */
	public function getChannels($option)
	: array{
		if (empty($option)){
			$option = self::OPT_BANK_TRANSFER;
		}

		return self::find()
		           ->andWhere(['status' => Status::STATUS_ACTIVE])
		           ->andWhere(['option' => $option])
		           ->asArray()
		           ->all();
	}

	/**
	 * @return mixed|null
	 */
	public function getFormPath(){
		if ($this->type == self::TYPE_WITHDRAW){
			$type = 'withdraw';
		}else{
			$type = 'deposit';
		}

		return $this->gateway->config['form'][$type]['view'] ?? NULL;
	}

	/**
	 * @return mixed|null
	 */
	public function getFormModel(){
		if ($this->type == self::TYPE_WITHDRAW){
			$type = 'withdraw';
		}else{
			$type = 'deposit';
		}

		return $this->gateway->config['form'][$type]['model'] ?? NULL;
	}
}