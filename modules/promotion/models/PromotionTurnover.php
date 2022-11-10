<?php

namespace modules\promotion\models;

use modules\customer\models\Customer;
use modules\wallet\models\WalletSub;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Exception;
use yii\db\Expression;

/**
 * This is the model class for table "{{%promotion_turnover}}".
 *
 * @property int $id
 * @property string $player_id
 * @property int $promotion_joining_id
 * @property string $wallet_code
 * @property string $date
 * @property float $turnover
 * @property int $round
 * @property float $win
 * @property int $created_at
 * @property int $updated_at
 *
 * @property-read \modules\promotion\models\PromotionJoining $promotionJoining
 * @property-read \modules\promotion\models\Promotion $promotion
 * @property-read WalletSub $wallet
 * @property-read Customer $player
 */
class PromotionTurnover extends ActiveRecord{

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%promotion_turnover}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['player_id', 'promotion_joining_id', 'wallet_code', 'date'], 'required'],
			[['promotion_joining_id', 'created_at', 'updated_at'], 'integer'],
			[['date'], 'safe'],
			[['turnover', 'win'], 'number'],
			[['player_id', 'wallet_code'], 'string', 'max' => 255],
			[['promotion_joining_id', 'date', 'wallet_code'], 'unique', 'targetAttribute' => ['promotion_joining_id', 'date', 'wallet_code']],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'                   => Yii::t('promotion', 'ID'),
			'player_id'            => Yii::t('promotion', 'Player'),
			'promotion_joining_id' => Yii::t('promotion', 'Promotion Joining'),
			'wallet_code'          => Yii::t('promotion', 'Wallet Code'),
			'win'                  => Yii::t('promotion', 'Win'),
			'date'                 => Yii::t('promotion', 'Date'),
			'turnover'             => Yii::t('promotion', 'Turnover'),
			'created_at'           => Yii::t('promotion', 'Created At'),
			'updated_at'           => Yii::t('promotion', 'Updated At'),
		];
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getPromotionJoining(){
		return $this->hasOne(PromotionJoining::class, ['id' => 'promotion_joining_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 * @throws \yii\base\InvalidConfigException
	 */
	public function getPromotion(){
		return $this->hasOne(Promotion::class, ['id' => 'promotion_id'])
		            ->viaTable(PromotionJoining::tableName(), ['id' => 'promotion_joining_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getPlayer(){
		return $this->hasOne(Customer::class, ['id' => 'player_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getWallet(){
		return $this->hasOne(WalletSub::class, ['id' => 'wallet_code', 'player_id' => 'player_id']);
	}

	/**
	 * @param $data
	 *
	 * @return bool|int
	 */
	public static function store($data){
		if (empty($data['turnover']) || empty($data['player_id'])){
			return TRUE;
		}

		try{
			return Yii::$app->db->createCommand()->upsert(self::tableName(), [
				'player_id'            => $data['player_id'] ?? NULL,
				'promotion_joining_id' => $data['promotion_joining_id'] ?? NULL,
				'wallet_code'          => $data['wallet_code'] ?? NULL,
				'date'                 => $data['date'] ?? NULL,
				'turnover'             => $data['turnover'],
				'win'                  => $data['win'],
				'round'                => 1,
				'created_at'           => time(),
				'updated_at'           => time(),
			], [
				'turnover'   => new Expression('turnover + :turnover',
					[':turnover' => $data['turnover']]),
				'win'        => new Expression('win + :win',
					[':win' => $data['win']]),
				'updated_at' => time(),
				'round'      => new Expression('round + 1'),
			])->execute();
		}catch (Exception $exception){
			return FALSE;
		}
	}
}
