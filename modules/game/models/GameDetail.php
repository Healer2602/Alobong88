<?php

namespace modules\game\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%game_detail}}".
 *
 * @property int $id
 * @property int $game_id
 * @property string $name
 * @property string $language
 * @property string $icon
 *
 * @property Game $game
 */
class GameDetail extends ActiveRecord{

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%game_detail}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['game_id'], 'required'],
			[['game_id'], 'integer'],
			[['icon'], 'string'],
			[['name'], 'string', 'max' => 255],
			[['language'], 'string', 'max' => 2],
			[['game_id'], 'exist', 'skipOnError' => TRUE, 'targetClass' => Game::class, 'targetAttribute' => ['game_id' => 'id']],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'       => Yii::t('game', 'ID'),
			'game_id'  => Yii::t('game', 'Game ID'),
			'name'     => Yii::t('game', 'Name'),
			'language' => Yii::t('game', 'Language'),
			'icon'     => Yii::t('game', 'Icon'),
		];
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getGame(){
		return $this->hasOne(Game::class, ['id' => 'game_id']);
	}

	/**
	 * @param array $data
	 *
	 * @return bool|int
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\db\Exception
	 */
	public static function upsert(array $data = []){
		$attributes = self::getTableSchema()->getColumnNames();

		if (self::validateMultiple($data, $attributes)){
			$game_id = ArrayHelper::getColumn($data, 'game_id');
			self::deleteAll(['game_id' => $game_id]);

			return Yii::$app->db->createCommand()
			                    ->batchInsert(self::tableName(), $attributes, $data)->execute();
		}

		return FALSE;
	}
}