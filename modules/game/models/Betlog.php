<?php

namespace modules\game\models;

use modules\customer\models\Customer;
use modules\promotion\models\PromotionTurnover;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Exception;

/**
 * This is the model class for table "{{%game_betlog}}".
 *
 * @property int $id
 * @property string $bet_id
 * @property string $provider
 * @property string $wallet_code
 * @property string $game_code
 * @property string $player_id
 * @property string $promotion_id
 * @property float $winloss
 * @property float $amount
 * @property float $valid_amount
 * @property float $bonus
 * @property float $turnover_bonus
 * @property float $turnover_wo_bonus
 * @property float $total_rebate
 * @property string|array $params
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Customer $customer
 * @property Vendor $vendor
 */
class Betlog extends ActiveRecord{

	const STATUS_OPEN = 1;

	const STATUS_SETTLED = 2;

	const STATUS_UNSETTLED = 3;

	const STATUS_CANCELLED = 4;

	const STATUS_UNCANCELLED = 7;

	const STATUS_CLOSED = 5;

	const STATUS_ADJUSTED = 6;

	const STATUS_RESETTLED = 7;

	const STATUS_NOT_RESETTLED = 8;

	public $bet_count = 0;

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%game_betlog}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['bet_id', 'provider', 'game_code', 'player_id'], 'string'],
			[['amount', 'valid_amount', 'bonus', 'total_rebate', 'turnover_bonus', 'turnover_wo_bonus', 'winloss'], 'number'],
			[['status', 'created_at', 'updated_at', 'promotion_id'], 'int'],
			[['player_id'], 'exist', 'skipOnError' => TRUE, 'targetClass' => Customer::class, 'targetAttribute' => ['player_id' => 'username']],
			['params', 'safe']
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'                => Yii::t('game', 'ID'),
			'provider'          => Yii::t('game', 'Game Provider'),
			'player_id'         => Yii::t('game', 'Player'),
			'game_code'         => Yii::t('game', 'Game'),
			'bet_count'         => Yii::t('game', 'Bet Count'),
			'amount'            => Yii::t('game', 'Bet Amount'),
			'valid_amount'      => Yii::t('game', 'Valid Bet Amount'),
			'winloss'           => Yii::t('game', 'WinLoss'),
			'turnover_bonus'    => Yii::t('game', 'Bonus Turnover'),
			'turnover_wo_bonus' => Yii::t('game', 'Turnover Without Bonus'),
			'bonus'             => Yii::t('game', 'Total Bonus'),
			'total_rebate'      => Yii::t('game', 'Total Rebate'),
			'created_at'        => Yii::t('game', 'Transaction Date'),
			'updated_at'        => Yii::t('game', 'Updated Date'),
		];
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getCustomer(){
		return $this->hasOne(Customer::class, ['username' => 'player_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 * @throws \yii\base\InvalidConfigException
	 */
	public function getVendor(){
		return $this->hasOne(Vendor::class, ['id' => 'vendor_id'])
		            ->viaTable(BetlogProvider::tableName(), ['code' => 'provider']);
	}

	/**
	 * @param array $data
	 *
	 * @return int|false
	 * @throws \yii\base\InvalidConfigException
	 */
	public static function store(array $data){
		try{
			$rate = self::findRate($data['wallet_code'] ?? NULL);

			$turnover       = min(abs($data['winloss'] ?? 0), $data['amount'] ?? 0) / $rate;
			$turnover_bonus = 0;
			$rebate         = 0;

			if (!empty($data['is_rebate']) && !empty($data['rate']) && !empty($data['winloss'])){
				if ($data['winloss'] > 0){
					$rebate = $turnover * $data['rate'];
				}else{
					$rebate = $data['winloss'] * $data['rate'];
				}
			}

			if (!empty($data['promotion_id'])){
				$turnover_bonus = $turnover;
				$turnover       = 0;
			}

			$stored = Yii::$app->db->createCommand()->upsert(self::tableName(), [
				'bet_id'            => $data['bet_id'] ?? NULL,
				'provider'          => $data['provider'] ?? 'N/A',
				'wallet_code'       => $data['wallet_code'] ?? NULL,
				'game_code'         => $data['game_code'] ?? NULL,
				'player_id'         => $data['player_id'] ?? NULL,
				'amount'            => ($data['amount'] ?? 0) / $rate,
				'valid_amount'      => abs($data['winloss'] ?? 0) / $rate,
				'winloss'           => ($data['winloss'] ?? 0) / $rate,
				'bonus'             => $data['bonus'] ?? 0,
				'turnover'          => $turnover + $turnover_bonus,
				'total_rebate'      => $rebate / $rate,
				'turnover_bonus'    => $turnover_bonus,
				'turnover_wo_bonus' => $turnover,
				'created_at'        => $data['created_at'] ?? NULL,
				'updated_at'        => $data['updated_at'] ?? NULL,
				'status'            => $data['status'] ?? NULL,
				'promotion_id'      => $data['promotion_id'] ?? NULL,
			], [
				'amount'            => ($data['amount'] ?? 0) / $rate,
				'valid_amount'      => abs($data['winloss'] ?? 0) / $rate,
				'winloss'           => ($data['winloss'] ?? 0) / $rate,
				'bonus'             => $data['bonus'] ?? 0,
				'turnover'          => $turnover + $turnover_bonus,
				'total_rebate'      => $rebate / $rate,
				'turnover_bonus'    => $turnover_bonus,
				'turnover_wo_bonus' => $turnover,
				'updated_at'        => $data['updated_at'] ?? NULL,
				'status'            => $data['status'] ?? NULL,
				'promotion_id'      => $data['promotion_id'] ?? NULL,
			])->execute();

			if (!empty($stored) && !empty($data['created_at'])){
				$date = Yii::$app->formatter->asDate($data['created_at'], 'php:Y-m-d');

				$turnover_data = [
					'player_id'            => $data['player_id'] ?? NULL,
					'wallet_code'          => $data['wallet_code'] ?? NULL,
					'promotion_joining_id' => $data['promotion_joining_id'] ?? NULL,
					'winloss'              => ($data['winloss'] ?? 0) / $rate,
					'date'                 => $date,
					'turnover'             => $turnover,
					'win'                  => 0
				];

				if ($data['winloss'] > 0){
					$turnover_data['win'] = $data['winloss'];
				}

				if (!empty($data['promotion_joining_id']) && !empty($turnover_bonus)){
					$turnover_data['turnover'] = $turnover_bonus;

					return PromotionTurnover::store($turnover_data);
				}

				if (!empty($turnover)){
					return Turnover::store($turnover_data);
				}
			}

			return $stored;

		}catch (Exception $exception){
			return FALSE;
		}
	}

	/**
	 * @param $code
	 *
	 * @return int|string
	 */
	private static function findRate($code){
		$rate = ProductWallet::find()
		                     ->select(['rate'])
		                     ->andWhere(['code' => $code])
		                     ->scalar();

		return empty($rate) ? 1 : $rate;
	}
}