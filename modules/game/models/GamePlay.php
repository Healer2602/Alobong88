<?php

namespace modules\game\models;

use modules\customer\models\Customer;
use modules\wallet\models\WalletSub;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Exception;

/**
 * This is the model class for table "{{%game_play}}".
 *
 * @property int $id
 * @property int $player_id
 * @property int $game_id
 * @property string $product_code
 * @property int $first_play
 * @property int $last_play
 *
 * @property-read \modules\game\models\Game $game
 * @property-read \modules\game\models\ProductWallet $productWallet
 * @property-read Customer $player
 * @property-read WalletSub $wallet
 */
class GamePlay extends ActiveRecord{

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%game_play}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['player_id', 'product_code', 'game_id'], 'required'],
			[['player_id', 'first_play', 'last_play'], 'integer'],
			[['product_code'], 'string', 'max' => 255],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'           => Yii::t('game', 'ID'),
			'player_id'    => Yii::t('game', 'Player'),
			'game_id'      => Yii::t('game', 'Game'),
			'product_code' => Yii::t('game', 'Product Code'),
			'first_play'   => Yii::t('game', 'First Play'),
			'last_play'    => Yii::t('game', 'Last Play'),
		];
	}

	/**
	 * @param $insert
	 *
	 * @return bool
	 */
	public function beforeSave($insert){
		if ($insert){
			$this->first_play = time();
			$this->last_play  = time();
		}else{
			$this->last_play = time();
		}

		return parent::beforeSave($insert);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getGame(){
		return $this->hasOne(Game::class, ['id' => 'game_id']);
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
	public function getProductWallet(){
		return $this->hasOne(ProductWallet::class, ['id' => 'product_code']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getWallet(){
		return $this->hasOne(WalletSub::class,
			['product_code' => 'product_code', 'player_id' => 'player_id']);
	}

	/**
	 * @param $data
	 *
	 * @return int|null
	 */
	public static function store($data){
		try{
			return Yii::$app->db->createCommand()->upsert(self::tableName(), [
				'player_id'    => $data['player_id'],
				'game_id'      => $data['game_id'],
				'product_code' => $data['product_code'],
				'first_play'   => time(),
				'last_play'    => time(),
			], [
				'last_play' => time(),
			])->execute();
		}catch (Exception $exception){
			return NULL;
		}
	}
}
