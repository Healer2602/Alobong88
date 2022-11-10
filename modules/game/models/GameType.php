<?php

namespace modules\game\models;

use common\base\Status;
use common\models\BaseActiveRecord;
use Yii;
use yii\behaviors\SluggableBehavior;

/**
 * This is the model class for table "{{%game_type}}".
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $icon
 * @property int $status
 * @property int $ordering
 * @property string $layout
 * @property int $created_at
 * @property int $created_by
 * @property int $updated_at
 * @property int $updated_by
 *
 * @property Game[] $games
 * @property \modules\game\models\VendorContent[] $vendorContents
 * @property array $statuses
 * @property array $layouts
 * @property array $url
 * @property array $layoutName
 */
class GameType extends BaseActiveRecord{

	public static $alias = 'type';

	const TYPE_DEFAULT = 'default';

	const TYPE_SPORT = 'sport';

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%game_type}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['icon', 'slug'], 'string'],
			[['status', 'created_at', 'created_by', 'updated_at', 'updated_by', 'ordering'], 'integer'],
			[['name', 'layout'], 'string', 'max' => 255],
			[['name'], 'required'],
			['ordering', 'default', 'value' => 1],
			['layout', 'default', 'value' => self::TYPE_DEFAULT],
			['slug', 'unique']
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
			'ordering'   => Yii::t('game', 'Ordering'),
			'created_at' => Yii::t('game', 'Created At'),
			'created_by' => Yii::t('game', 'Created By'),
			'updated_at' => Yii::t('game', 'Updated At'),
			'updated_by' => Yii::t('game', 'Updated By'),
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
	public function getGames(){
		return $this->hasMany(Game::class, ['type_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getVendorContents(){
		return $this->hasMany(VendorContent::class, ['type_id' => 'id']);
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
	 * @return array
	 */
	public function getLayouts(){
		return self::listLayout();
	}

	/**
	 * @return array
	 */
	public static function listLayout(){
		return [
			self::TYPE_DEFAULT => Yii::t('game', 'Default'),
			self::TYPE_SPORT   => Yii::t('game', 'Sports/eSports'),
		];
	}

	/**
	 * @return bool
	 */
	public function isUsed(){
		$game    = Game::find()->andWhere(['type_id' => $this->id])->exists();
		$content = VendorContent::find()->andWhere(['type_id' => $this->id])->exists();
		if ($game || $content){
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return array
	 */
	public function getUrl(){
		return ['/game/type/index', 'slug' => $this->slug];
	}

	/**
	 * @return mixed|string
	 */
	public function getLayoutName(){
		return $this->layouts[$this->layout] ?? $this->layout;
	}
}