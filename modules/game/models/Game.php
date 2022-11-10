<?php

namespace modules\game\models;

use common\base\Status;
use common\models\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%game}}".
 *
 * @property int $id
 * @property int $vendor_id
 * @property int $type_id
 * @property string $name
 * @property string $code
 * @property string $icon
 * @property string $lines
 * @property string $rtp
 * @property double $min_bet
 * @property double $max_bet
 * @property int $status
 * @property int $free_to_play
 * @property int $feature
 * @property int $ordering
 * @property int $created_at
 * @property int $created_by
 * @property int $updated_at
 * @property int $updated_by
 *
 * @property GameType $type
 * @property Vendor $vendor
 * @property \modules\game\models\ProductWallet $productWallet
 * @property GameDetail[] $details
 * @property GameDetail $detailZh
 * @property GameDetail $detailVi
 * @property array $types
 * @property array $vendors
 * @property array $statuses
 * @property array $url
 * @property array $tryUrl
 * @property array $nameLabel
 * @property array $iconUrl
 */
class Game extends BaseActiveRecord{

	public static $alias = 'game';

	const LANGUAGE_ZH = 'zh';

	const LANGUAGE_VI = 'vi';

	const FEATURED = 1;

	const FREE_TO_PLAY_VALUE = 1;

	const FREE_TO_PLAY_KEY = 'YES';

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%game}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['vendor_id', 'type_id', 'name', 'code'], 'required'],
			[['vendor_id', 'type_id', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by', 'ordering', 'feature', 'free_to_play'], 'integer'],
			[['icon', 'lines', 'rtp'], 'string'],
			[['min_bet', 'max_bet'], 'double'],
			[['code'], 'unique'],
			[['name', 'code'], 'string', 'max' => 255],
			[['type_id'], 'exist', 'skipOnError' => TRUE, 'targetClass' => GameType::class, 'targetAttribute' => ['type_id' => 'id']],
			[['vendor_id'], 'exist', 'skipOnError' => TRUE, 'targetClass' => Vendor::class, 'targetAttribute' => ['vendor_id' => 'id']],
			['ordering', 'default', 'value' => 1],
			['free_to_play', 'default', 'value' => 0],
		];
	}

	/**
	 * @return array
	 */
	public function behaviors(){
		$behaviors                    = parent::behaviors();
		$behaviors['audit']['module'] = Yii::t('game', 'Game');

		return $behaviors;
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'           => Yii::t('game', 'ID'),
			'vendor_id'    => Yii::t('game', 'Vendor'),
			'type_id'      => Yii::t('game', 'Type'),
			'name'         => Yii::t('game', 'Name'),
			'code'         => Yii::t('game', 'Code'),
			'icon'         => Yii::t('game', 'Icon'),
			'lines'        => Yii::t('game', 'Lines'),
			'min_bet'      => Yii::t('game', 'Min Bet'),
			'max_bet'      => Yii::t('game', 'Max Bet'),
			'rtp'          => Yii::t('game', 'RTP'),
			'feature'      => Yii::t('game', 'Feature'),
			'free_to_play' => Yii::t('game', 'Free To Play'),
			'status'       => Yii::t('game', 'Status'),
			'ordering'     => Yii::t('game', 'Ordering'),
			'created_at'   => Yii::t('game', 'Created At'),
			'created_by'   => Yii::t('game', 'Created By'),
			'updated_at'   => Yii::t('game', 'Updated At'),
			'updated_by'   => Yii::t('game', 'Updated By'),
		];
	}

	/**
	 * @param bool $insert
	 * @param array $changedAttributes
	 */
	public function afterSave($insert, $changedAttributes){
		if ($insert){
			self::updateAllCounters(['ordering' => 1], "id <> :id", [':id' => $this->id]);
		}

		parent::afterSave($insert, $changedAttributes);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getType(){
		return $this->hasOne(GameType::class, ['id' => 'type_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getVendor(){
		return $this->hasOne(Vendor::class, ['id' => 'vendor_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getDetails(){
		return $this->hasMany(GameDetail::class, ['game_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getProductWallet(){
		return $this->hasOne(ProductWallet::class, ['vendor_id' => 'vendor_id'])
		            ->andOnCondition(['type_id' => ['', NULL, $this->type_id]]);
	}

	/**
	 * @return array
	 * @throws \Throwable
	 */
	public function getTypes(){
		return GameType::findList();
	}

	/**
	 * @return array
	 * @throws \Throwable
	 */
	public function getVendors(){
		return Vendor::findList();
	}

	/**
	 * @return array
	 */
	public function getStatuses(){
		return [
			Status::STATUS_ACTIVE   => Yii::t('game', 'Active'),
			Status::STATUS_INACTIVE => Yii::t('game', 'Inactive')
		];
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getDetailZh(){
		return $this->hasOne(GameDetail::class, ['game_id' => 'id'])
		            ->andOnCondition(['language' => self::LANGUAGE_ZH]);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getDetailVi(){
		return $this->hasOne(GameDetail::class, ['game_id' => 'id'])
		            ->andOnCondition(['language' => self::LANGUAGE_VI]);
	}

	/**
	 * @return array
	 */
	public function getUrl(){
		return ['/game/default/index', 'id' => $this->id];
	}

	/**
	 * @return array
	 */
	public function getTryUrl(){
		return ['/game/default/try', 'id' => $this->id];
	}

	/**
	 * @return string
	 */
	public function getNameLabel(){
		switch (Yii::$app->language){
			case 'zh':
			case 'tw':
			case 'cn':
				$name = $this->detailZh->name ?? NULL;
				break;
			case 'vi':
				$name = $this->detailVi->name ?? NULL;
				break;
		}

		return empty($name) ? $this->name : $name;
	}

	/**
	 * @return string
	 */
	public function getIconUrl(){
		switch (Yii::$app->language){
			case 'zh':
			case 'tw':
			case 'cn':
				$icon = $this->detailZh->icon ?? NULL;
				break;
			case 'vi':
				$icon = $this->detailVi->icon ?? NULL;
				break;
		}

		return empty($icon) ? $this->icon : $icon;
	}
}