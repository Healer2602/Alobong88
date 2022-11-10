<?php

namespace modules\promotion\models;

use modules\customer\models\Customer;
use modules\wallet\models\WalletSub;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Html;

/**
 * This is the model class for table "{{%promotion_joining}}".
 *
 * @property int $id
 * @property string $player_id
 * @property int $wallet_id
 * @property string $wallet_code
 * @property int $promotion_id
 * @property string $promotion_type
 * @property float $rate
 * @property float $bonus
 * @property int $joined_at
 * @property int $expired_at
 * @property int $reset
 * @property int $status
 * @property string|array $params
 *
 * @property-read WalletSub $wallet
 * @property-read Customer $player
 * @property-read \modules\promotion\models\Promotion $promotion
 * @property-read \modules\promotion\models\PromotionTurnover $turnover
 */
class PromotionJoining extends ActiveRecord{

	const STATUS_RUNNING = 0;

	const STATUS_DONE = 10;

	const STATUS_FAILED = 5;

	const STATUS_CANCELED = 15;

	public $totalTurnover = 0;
	public $totalWin = 0;
	public $round = 0;
	public $total = 0;

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%promotion_joining}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['player_id', 'joined_at', 'expired_at'], 'required'],
			[['wallet_id', 'promotion_id', 'joined_at', 'expired_at'], 'integer'],
			[['params', 'wallet_code', 'player_id', 'promotion_type'], 'string'],
			[['rate', 'bonus', 'status'], 'number'],
			['reset', 'boolean'],
			['status', 'default', 'value' => self::STATUS_RUNNING]
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'           => Yii::t('promotion', 'ID'),
			'player_id'    => Yii::t('promotion', 'Player'),
			'wallet_id'    => Yii::t('promotion', 'Wallet'),
			'wallet_code'  => Yii::t('promotion', 'Product Code'),
			'promotion_id' => Yii::t('promotion', 'Promotion'),
			'joined_at'    => Yii::t('promotion', 'Joined At'),
			'expired_at'   => Yii::t('promotion', 'Expired At'),
			'params'       => Yii::t('promotion', 'Params'),
			'rate'         => Yii::t('promotion', 'Rate'),
			'bonus'        => Yii::t('promotion', 'Bonus'),
			'status'       => Yii::t('promotion', 'Status'),
		];
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getPromotion(){
		return $this->hasOne(Promotion::class, ['id' => 'promotion_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getPlayer(){
		return $this->hasOne(Customer::class, ['username' => 'player_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getWallet(){
		return $this->hasOne(WalletSub::class, ['id' => 'wallet_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getTurnover(){
		return $this->hasMany(PromotionTurnover::class, ['promotion_joining_id' => 'id']);
	}

	/**
	 * @param array $data
	 *
	 * @return bool
	 */
	public static function store(array $data)
	: ?bool{
		try{
			$promotion = $data['promotion'] ?? Promotion::findOne($data['promotion_id']);
			if (!empty($promotion) && $promotion->isRunning()){
				$expired_at = $promotion->end_date;
				if (!empty($promotion->maximum_promotion)){
					$expired_at = time() + $promotion->maximum_promotion * 86400;
				}

				$model = new static([
					'player_id'      => $data['player_id'],
					'promotion_id'   => $data['promotion_id'],
					'promotion_type' => $promotion->type,
					'wallet_id'      => $data['wallet_id'],
					'wallet_code'    => strval($data['wallet_code']),
					'reset'          => FALSE,
					'joined_at'      => time(),
					'expired_at'     => $expired_at,
					'rate'           => $promotion->bonus_rate / 100,
					'bonus'          => $data['bonus'] ?? 0
				]);

				return $model->save();
			}

			return FALSE;
		}catch (\Exception $exception){
			return FALSE;
		}
	}

	/**
	 * @param $player_id
	 * @param $wallet_code
	 *
	 * @return array
	 */
	public static function joiningPromotion($player_id, $wallet_code){
		return PromotionJoining::find()
		                       ->andWhere(['player_id' => $player_id, 'wallet_code' => $wallet_code])
		                       ->andWhere(['>=', 'expired_at', time()])
		                       ->asArray()
		                       ->one();
	}

	/**
	 * @return array
	 */
	public static function statuses(){
		return [
			self::STATUS_RUNNING  => Yii::t('promotion', 'Running'),
			self::STATUS_DONE     => Yii::t('promotion', 'Done'),
			self::STATUS_FAILED   => Yii::t('promotion', 'Failed'),
			self::STATUS_CANCELED => Yii::t('promotion', 'Cancelled'),
		];
	}

	/**
	 * @return string
	 */
	public function getStatusLabel(){
		if ($this->status == self::STATUS_RUNNING){
			$class = 'bg-primary';
		}elseif ($this->status == self::STATUS_DONE){
			$class = 'bg-success';
		}else{
			$class = 'bg-danger';
		}

		return Html::tag('span', self::statuses()[$this->status] ?? NULL, [
			'class' => ['badge', $class]]);
	}


	/**
	 * @return bool
	 */
	public function canCancel(){
		return !empty($this->promotion) && $this->status == self::STATUS_RUNNING && $this->joined_at <= time() and $this->expired_at >= time();
	}
}
