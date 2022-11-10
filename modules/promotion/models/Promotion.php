<?php

namespace modules\promotion\models;

use common\base\AppHelper;
use common\models\BaseActiveRecord;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%promotion}}".
 *
 * @property int $id
 * @property string $name
 * @property string $type
 * @property int $start_date
 * @property int $end_date
 * @property int $min_round
 * @property double $bonus_rate
 * @property double $min_deposit
 * @property double $max_bonus
 * @property double $min_bonus
 * @property double $refund_amount
 * @property int $maximum_promotion
 * @property int $number_promotion
 * @property string $excluding_revenue
 * @property string $exclude_promotion
 * @property int $status
 * @property int $created_at
 * @property int $created_by
 * @property int $updated_at
 * @property int $updated_by
 *
 * @property array $params
 * @property array $excludingRevenues
 * @property array $excludePromotions
 * @property string $statusLabel
 * @property string $typeLabel
 * @property array $productWallets
 * @property PromotionWallet[] $productMap
 */
class Promotion extends BaseActiveRecord{

	const TYPE_FIRST_DEPOSIT = 'first_deposit';

	const TYPE_REFUND_LOSING = 'refund_losing';

	const TYPE_REBATE_1H = 'rebate_1h';

	const STATUS_STOPPED = - 5;

	const STATUS_RUNNING = 10;

	const STATUS_PAUSE = 15;

	const STATUS_HIDDEN = 20;

	public $date = '';

	public $product_wallet;

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%promotion}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['start_date', 'end_date', 'min_round', 'maximum_promotion', 'number_promotion', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
			[['bonus_rate', 'min_deposit', 'max_bonus', 'min_bonus', 'refund_amount'], 'number'],
			[['type', 'name'], 'string', 'max' => 255],
			[['date'], 'string'],
			[['exclude_promotion', 'excluding_revenue', 'product_wallet'], 'safe'],
			[['name', 'date', 'product_wallet', 'bonus_rate'], 'required']
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'                => Yii::t('promotion', 'ID'),
			'name'              => Yii::t('promotion', 'Name'),
			'type'              => Yii::t('promotion', 'Type'),
			'start_date'        => Yii::t('promotion', 'Start Date'),
			'end_date'          => Yii::t('promotion', 'End Date'),
			'min_round'         => Yii::t('promotion', 'Min Round'),
			'bonus_rate'        => Yii::t('promotion', 'Bonus Rate'),
			'min_deposit'       => Yii::t('promotion', 'Min Deposit'),
			'max_bonus'         => Yii::t('promotion', 'Max Bonus'),
			'min_bonus'         => Yii::t('promotion', 'Min Bonus'),
			'refund_amount'     => Yii::t('promotion', 'Max Refund Amount'),
			'maximum_promotion' => Yii::t('promotion', 'Maximum Promotion'),
			'number_promotion'  => Yii::t('promotion', 'Maximum number of participation'),
			'excluding_revenue' => Yii::t('promotion', 'Excluding Revenue'),
			'exclude_promotion' => Yii::t('promotion', 'Promotion Do Not Apply To'),
			'status'            => Yii::t('promotion', 'Status'),
			'created_at'        => Yii::t('promotion', 'Created At'),
			'created_by'        => Yii::t('promotion', 'Created By'),
			'updated_at'        => Yii::t('promotion', 'Updated At'),
			'updated_by'        => Yii::t('promotion', 'Updated By'),
			'date'              => Yii::t('promotion', 'Date'),
			'product_wallet'    => Yii::t('promotion', 'Product'),
		];
	}

	/**
	 * @return array
	 */
	public function attributeHints(){
		$hints = [
			'date' => Yii::t('promotion', 'Start Date to End Date'),
		];

		return ArrayHelper::merge($hints, parent::attributeHints());
	}

	/**
	 * @param $insert
	 *
	 * @return bool
	 */
	public function beforeSave($insert){
		if ($this->date){
			$date_range = AppHelper::parseDateRange($this->date);

			if (!empty($date_range[0])){
				$this->start_date = $date_range[0];
			}
			if (!empty($date_range[1])){
				$this->end_date = $date_range[1];
			}
		}

		if (is_array($this->excluding_revenue)){
			$this->excluding_revenue = Json::encode($this->excluding_revenue);
		}

		if (is_array($this->exclude_promotion)){
			$this->exclude_promotion = Json::encode($this->exclude_promotion);
		}

		return parent::beforeSave($insert);
	}

	/**
	 * @param $insert
	 * @param $changedAttributes
	 *
	 * @return void
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\db\Exception
	 */
	public function afterSave($insert, $changedAttributes){
		if ($this->id){
			$this->storeProductWalletMap();
		}
		parent::afterSave($insert, $changedAttributes);
	}

	/**
	 * @return void
	 * @throws \yii\base\InvalidConfigException
	 */
	public function afterFind(){
		if ($this->exclude_promotion){
			$this->exclude_promotion = Json::decode($this->exclude_promotion);
		}

		if ($this->excluding_revenue){
			$this->excluding_revenue = Json::decode($this->excluding_revenue);
		}

		if ($this->start_date){
			$this->date = Yii::$app->formatter->asDatetime($this->start_date) . ' to ' . Yii::$app->formatter->asDatetime($this->end_date ?? $this->start_date);
		}

		parent::afterFind();
	}

	/**
	 * @return array
	 */
	public static function types(){
		return [
			self::TYPE_FIRST_DEPOSIT => Yii::t('promotion', 'First deposit bonus'),
			self::TYPE_REBATE_1H     => Yii::t('promotion', 'Rebate 1 hour'),
			self::TYPE_REFUND_LOSING => Yii::t('promotion', 'Refund when losing game'),
		];
	}

	/**
	 * @return array
	 */
	public function getParams(){
		return [
			'excluding_revenue' => [
				'draw'         => Yii::t('promotion', 'Draw bets'),
				'canceled'     => Yii::t('promotion', 'Canceled bets'),
				'void'         => Yii::t('promotion', 'Void bets'),
				'two_way'      => Yii::t('promotion', 'Two-way bets'),
				'less_0_5'     => Yii::t('promotion', 'Odds less than 0.5'),
				'malaysia_0_5' => Yii::t('promotion', 'Malaysia less ±0.5'),
				'dec_1_5'      => Yii::t('promotion', 'DEC less 1.5'),
			],
			'exclude_promotion' => [
				'refund'        => Yii::t('promotion', 'Refund'),
				'refund_losing' => Yii::t('promotion', 'Refund when losing game'),
			]
		];
	}

	/**
	 * @return array|mixed
	 */
	public function getExcludingRevenues(){
		return $this->params['excluding_revenue'];
	}

	/**
	 * @return array|mixed
	 */
	public function getExcludePromotions(){
		return $this->params['exclude_promotion'];
	}

	/**
	 * @return array
	 */
	public static function statuses(){
		return [
			self::STATUS_RUNNING => Yii::t('promotion', 'Running'),
			self::STATUS_STOPPED => Yii::t('promotion', 'Stopped'),
			self::STATUS_PAUSE   => Yii::t('promotion', 'Pause'),
			self::STATUS_HIDDEN  => Yii::t('promotion', 'Hidden'),
		];
	}

	/**
	 * @return string
	 */
	public function getStatusLabel(){
		switch ($this->status){
			case self::STATUS_RUNNING:
				$class = 'bg-success';
				break;
			case self::STATUS_STOPPED:
				$class = 'bg-danger';
				break;
			case self::STATUS_PAUSE:
				$class = 'bg-primary';
				break;
			case self::STATUS_HIDDEN:
				$class = 'bg-secondary';
				break;
			default:
				$class = '';
		}

		return Html::tag('span', self::statuses()[$this->status] ?? NULL, [
			'class' => [
				'badge',
				$class
			]]);
	}

	/**
	 * @return mixed|null
	 */
	public function getTypeLabel(){
		return self::types()[$this->type] ?? NULL;
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getProductMap(){
		return $this->hasMany(PromotionWallet::class, ['promotion_id' => 'id']);
	}

	/**
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\db\Exception
	 */
	private function storeProductWalletMap(){
		PromotionWallet::deleteAll(['promotion_id' => $this->id]);
		if ($product_wallets = $this->product_wallet){
			if (!is_array($product_wallets)){
				$product_wallets = [$product_wallets];
			}
			if (!empty($product_wallets)){
				$data = [];
				foreach ($product_wallets as $product_code){
					$data[] = new PromotionWallet([
						'promotion_id' => $this->id,
						'product_code' => $product_code
					]);
				}

				PromotionWallet::upsert($data);
			}
		}
	}

	/**
	 * @return array
	 * @throws \Throwable
	 */
	public function getProductWallets()
	: ?array{
		return ProductWallet::list();
	}

	private static $_running = NULL;

	/**
	 * @return array|\modules\promotion\models\Promotion[]|\yii\db\ActiveRecord[]|null
	 */
	public static function running(){
		if (self::$_running === NULL){
			self::$_running = self::find()
			                      ->with('productMap')
			                      ->andWhere(['status' => self::STATUS_RUNNING])
			                      ->andWhere(['<=', 'start_date', time()])
			                      ->andWhere(['>=', 'end_date', time()])
			                      ->orderBy(['start_date' => SORT_ASC])
			                      ->asArray()
			                      ->all() ?? [];
		}

		return self::$_running;
	}

	/**
	 * @return bool
	 */
	public function isRunning(){
		return $this->status == self::STATUS_RUNNING && $this->start_date <= time() && $this->end_date >= time();
	}

	/**
	 * @return bool
	 */
	public function willLocked(){
		return $this->type == self::TYPE_FIRST_DEPOSIT;
	}
}