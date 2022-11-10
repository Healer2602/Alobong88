<?php

namespace modules\game\models;

use modules\customer\models\Customer;
use modules\wallet\models\Wallet;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Exception;
use yii\db\Expression;

/**
 * This is the model class for table "{{%game_turnover}}".
 *
 * @property int $id
 * @property string $player_id
 * @property string $wallet_code
 * @property string $date
 * @property string $turnover
 * @property string $winloss
 * @property int $created_at
 * @property int $updated_at
 *
 * @property-read Customer $player
 * @property-read \modules\game\models\ProductWallet $wallet
 */
class Turnover extends ActiveRecord{

	public $total = 0;
	public $name;
	public $email;

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%game_turnover}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['player_id', 'wallet_code', 'date'], 'required'],
			[['created_at', 'updated_at'], 'integer'],
			[['date'], 'safe'],
			[['turnover', 'winloss'], 'number'],
			[['wallet_code', 'player_id'], 'string', 'max' => 255],
			[['player_id', 'wallet_code', 'date'], 'unique', 'targetAttribute' => ['player_id', 'wallet_code', 'date']],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'          => Yii::t('game', 'ID'),
			'player_id'   => Yii::t('game', 'Player'),
			'wallet_code' => Yii::t('game', 'Game Code'),
			'date'        => Yii::t('game', 'Date'),
			'turnover'    => Yii::t('game', 'Turnover'),
			'winloss'     => Yii::t('game', 'Winloss'),
			'created_at'  => Yii::t('game', 'Created At'),
			'updated_at'  => Yii::t('game', 'Updated At'),
		];
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
		return $this->hasOne(ProductWallet::class, ['code' => 'wallet_code']);
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
			$stored = Yii::$app->db->createCommand()->upsert(self::tableName(), [
				'player_id'   => $data['player_id'] ?? NULL,
				'wallet_code' => $data['wallet_code'] ?? NULL,
				'date'        => $data['date'] ?? NULL,
				'turnover'    => $data['turnover'],
				'winloss'     => $data['winloss'] ?? 0,
				'created_at'  => time(),
				'updated_at'  => time(),
			], [
				'turnover'   => new Expression('turnover + :turnover',
					[':turnover' => $data['turnover']]),
				'winloss'    => new Expression('winloss + :winloss',
					[':winloss' => $data['winloss'] ?? 0]),
				'updated_at' => time(),
			])->execute();

			if (!empty($stored)){
				return Wallet::storeTurnover($data['player_id'], $data['turnover']);
			}

			return $stored;
		}catch (Exception $exception){
			return FALSE;
		}
	}
}
