<?php

namespace modules\website\models;

use backend\base\MenuHelper;
use common\base\ActiveQuery;
use common\base\Status;
use common\models\BaseActiveRecord;
use creocoder\nestedsets\NestedSetsBehavior;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%menu}}".
 *
 * @property int $id
 * @property string $name
 * @property int $menu_path
 * @property int $parent_id
 * @property int $status
 * @property int $created_at
 * @property int $created_by
 * @property int $updated_at
 * @property int $updated_by
 * @property string|array $params
 * @property string $language
 * @property string $position
 * @property string|\yii\web\UploadedFile $icon
 * @property int $lft [int(11)]
 * @property int $rgt [int(11)]
 * @property int $depth [int(11) unsigned]
 * @property int $tree [int(11)]
 *
 * @property array $parents
 * @property array $positions
 * @property string $coreMenu
 * @property string $positionLabel
 * @property Menu $parent
 * @property array $styles
 *
 * @method ActiveQuery children()
 * @method ActiveQuery parents()
 * @method ActiveQuery leaves()
 * @method ActiveQuery roots()
 */
class Menu extends BaseActiveRecord{

	public static $alias = 'menu';

	/**
	 * @inheritdoc
	 */
	public static function tableName(){
		return '{{%menu}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules(){
		return [
			[['name'], 'required'],
			[['name', 'position'], 'string', 'max' => 255],
			[['parent_id', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
			[['menu_path', 'language', 'icon'], 'string'],
			[['menu_path'], 'default', 'value' => '#'],
			[['tree'], 'default', 'value' => 0],
			[['parent_id'], 'exist', 'skipOnError' => TRUE, 'targetClass' => Menu::class, 'targetAttribute' => ['parent_id' => 'id']],
			['params', 'safe']
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels(){
		return [
			'name'       => Yii::t('common', 'Name'),
			'menu_path'  => Yii::t('common', 'Link'),
			'parent_id'  => Yii::t('common', 'Parent'),
			'status'     => Yii::t('common', 'Status'),
			'language'   => Yii::t('common', 'Language'),
			'created_at' => Yii::t('common', 'Created at'),
			'created_by' => Yii::t('common', 'Created By'),
			'updated_at' => Yii::t('common', 'Updated At'),
			'updated_by' => Yii::t('common', 'Updated By'),

			'position' => Yii::t('website', 'Position'),
		];
	}

	/**
	 * @return array
	 */
	public function behaviors(){
		$behaviors         = parent::behaviors();
		$behaviors['tree'] = [
			'class'          => NestedSetsBehavior::class,
			'treeAttribute'  => 'tree',
			'leftAttribute'  => 'lft',
			'rightAttribute' => 'rgt',
			'depthAttribute' => 'depth',
		];

		$behaviors['audit']['module'] = Yii::t('common', 'Website');

		return $behaviors;
	}

	/**
	 * @return array
	 */
	public function transactions(){
		return [
			self::SCENARIO_DEFAULT => self::OP_ALL,
		];
	}

	/**
	 * @return \modules\website\models\MenuQuery
	 */
	public static function find(){
		return new MenuQuery(get_called_class());
	}

	/**
	 * @return bool
	 */
	public function beforeValidate(){
		if (empty($this->parent_id)){
			$this->parent_id = NULL;
		}

		return parent::beforeValidate();
	}

	/**
	 * @param bool $insert
	 *
	 * @return bool
	 */
	public function beforeSave($insert){
		if (is_array($this->params)){
			$this->params = Json::encode($this->params);
		}

		return parent::beforeSave($insert);
	}

	/**
	 * @inheritDoc
	 */
	public function afterFind(){
		if (is_string($this->params)){
			$this->params = Json::decode($this->params);
		}

		parent::afterFind();
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getParent(){
		return $this->hasOne(self::class, ['id' => 'parent_id']);
	}

	/**
	 * @param int $except
	 *
	 * @return array
	 */
	public function getMenuOptions($except = 0){
		if (!empty($except)){
			$except = $this->findOne($except);
		}

		$items = MenuHelper::traverseNestedOption($this->children()->all(), $except);

		return [0 => Yii::t('common', 'Root')] + $items;
	}

	/**
	 * @return array|\yii\db\ActiveRecord[]
	 */
	public function getParents(){
		return $this->parents()->asArray()->all();
	}

	/**
	 * @return bool
	 */
	public function isRelated(){
		return $this->children()->count() > 0;
	}

	/**
	 * @return bool|string
	 */
	public function getCoreMenu(){
		if (empty($this->params)){
			return FALSE;
		}

		$menu = [
			$this->params['app']['label'] ?? NULL,
			$this->params['item']['label'] ?? NULL,
		];

		return implode(' > ', array_filter($menu));
	}

	/**
	 * @return array
	 */
	public function getPositions(){
		return Yii::$app->params['menu.positions'] ?? [];
	}

	/**
	 * @return mixed|string
	 */
	public function getPositionLabel(){
		return $this->positions[$this->position] ?? Yii::t('common', '(Unassigned)');
	}

	/**
	 * @return string|\yii\web\UploadedFile
	 */
	public function getIconLink(){
		return $this->icon;
	}

	/**
	 * @param string $position
	 *
	 * @return string
	 */
	public static function cacheKey($position = 'default'){
		return "menu-cache-{$position}";
	}

	/**
	 * @return array
	 */
	public function getStyles(){
		return [
			'default'   => Yii::t('common', 'Default'),
			'large'     => Yii::t('common', 'Large'),
			'fullwidth' => Yii::t('common', 'Full width'),
		];
	}

	/**
	 * @param string $position
	 *
	 * @return array|mixed
	 * @throws \Throwable
	 */
	public static function findByPosition($position = ''){
		$root = self::getDb()->cache(function () use ($position){
			return static::find()
			             ->roots()
			             ->translate()
			             ->andWhere(['status' => Status::STATUS_ACTIVE, 'position' => $position])
			             ->one();

		}, 0, new TagDependency([
			'tags' => [
				self::cacheKey($position . '-' . Yii::$app->id),
				Yii::$app->language
			],
		]));

		if (!$root){
			return [];
		}

		return $root->children()
		            ->asArray()
		            ->all();
	}
}
