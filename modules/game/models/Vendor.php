<?php

namespace modules\game\models;

use common\base\Status;
use common\models\BaseActiveRecord;
use modules\customer\models\Setting;
use modules\wallet\models\WalletSub;
use Yii;
use yii\behaviors\SluggableBehavior;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%vendor}}".
 *
 * @property int $id
 * @property string $name
 * @property string $icon
 * @property string $slug
 * @property string $currency
 * @property int $status
 * @property int $ordering
 * @property int $created_at
 * @property int $created_by
 * @property int $updated_at
 * @property int $updated_by
 *
 * @property Game[] $games
 * @property array $statuses
 * @property array $currencies
 * @property array $url
 * @property WalletSub $walletSub
 * @property-read \modules\customer\models\Setting $setting
 */
class Vendor extends BaseActiveRecord{

	public static $alias = 'vendor';

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%vendor}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['icon'], 'string'],
			[['status', 'created_at', 'created_by', 'updated_at', 'updated_by', 'ordering'], 'integer'],
			[['name', 'slug'], 'string', 'max' => 255],
			['name', 'required'],
			['ordering', 'default', 'value' => 1],
			['currency', 'safe']
		];
	}

	/**
	 * @return array
	 */
	public function behaviors(){
		$behaviors                    = parent::behaviors();
		$behaviors['audit']['module'] = Yii::t('game', 'Game');

		$behaviors['slug'] = [
			'class'        => SluggableBehavior::class,
			'ensureUnique' => TRUE,
			'attribute'    => 'name'
		];

		return $behaviors;
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'         => Yii::t('game', 'ID'),
			'name'       => Yii::t('game', 'Name'),
			'icon'       => Yii::t('game', 'Icon'),
			'status'     => Yii::t('game', 'Status'),
			'currency'   => Yii::t('game', 'Currency'),
			'ordering'   => Yii::t('game', 'Ordering'),
			'created_at' => Yii::t('game', 'Created At'),
			'created_by' => Yii::t('game', 'Created By'),
			'updated_at' => Yii::t('game', 'Updated At'),
			'updated_by' => Yii::t('game', 'Updated By'),
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
	public function getGames(){
		return $this->hasMany(Game::class, ['vendor_id' => 'id']);
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
	 * @return bool
	 */
	public function isUsed()
	: ?bool{
		$game     = Game::find()->andWhere(['vendor_id' => $this->id])->exists();
		$content  = VendorContent::find()->andWhere(['vendor_id' => $this->id])->exists();
		$provider = BetlogProvider::find()->andWhere(['vendor_id' => $this->id])->exists();

		return $game || $content || $provider;
	}

	/**
	 * @return array
	 */
	public function getUrl(){
		return ['/game/vendor/view', 'slug' => $this->slug];
	}

	/**
	 * @return array
	 */
	public function getCurrencies(){
		return $this->setting->listCurrency ?? [];
	}

	/**
	 * @var null
	 */
	private $_setting = NULL;

	/**
	 * @return \modules\customer\models\Setting
	 * @throws \Throwable
	 */
	public function getSetting(){
		if ($this->_setting === NULL){
			$setting = new Setting();
			$setting->getValues();

			$this->_setting = $setting;
		}

		return $this->_setting;
	}
}