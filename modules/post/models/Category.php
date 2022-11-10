<?php

namespace modules\post\models;

use common\base\AppHelper;
use common\models\BaseActiveRecord;
use Yii;
use yii\behaviors\SluggableBehavior;
use yii\helpers\Inflector;
use yii\helpers\Url;

/**
 * This is the model class for table "{{%post_category}}".
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property string $type
 * @property string $language
 * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 *
 * @property Post[] $posts
 * @property array $url
 * @property-read array $publicUrls
 */
class Category extends BaseActiveRecord{

	const TYPE = 'post';

	public static $alias = 'category';

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%post_category}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['name'], 'required'],
			[['description', 'language', 'type'], 'string'],
			[['status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
			[['name', 'slug'], 'string', 'max' => 255],
			[['description'], 'string', 'max' => 2000],
			[['slug'], 'unique'],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'name' => Yii::t('post', 'Category name'),

			'id'          => Yii::t('common', 'ID'),
			'slug'        => Yii::t('common', 'Slug'),
			'description' => Yii::t('common', 'Description'),
			'status'      => Yii::t('common', 'Status'),
			'created_by'  => Yii::t('common', 'Created By'),
			'created_at'  => Yii::t('common', 'Created at'),
			'updated_by'  => Yii::t('common', 'Updated By'),
			'updated_at'  => Yii::t('common', 'Updated At'),
		];
	}

	/**
	 * @return \common\base\ActiveQuery
	 */
	public static function find(){
		return parent::find()->andWhere([static::$alias . '.type' => static::TYPE]);
	}

	/**
	 * @return array
	 */
	public function behaviors(){
		$behaviors = parent::behaviors();

		$behaviors['slug'] = [
			'class'        => SluggableBehavior::class,
			'ensureUnique' => TRUE,
			'attribute'    => 'name'
		];

		$behaviors['audit']['module'] = Yii::t('post', 'Category');

		return $behaviors;
	}

	/**
	 * @param bool $insert
	 *
	 * @return bool
	 */
	public function beforeSave($insert){
		if (!empty($this->slug)){
			$this->slug = Inflector::slug($this->slug);
			$this->detachBehavior('slug');
		}

		$this->type = static::TYPE;

		return parent::beforeSave($insert);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getPosts(){
		return $this->hasMany(Post::class, ['category_id' => 'id'])
		            ->where([Post::$alias . '.type' => $this->type]);
	}

	/**
	 * @return array
	 */
	public function getUrl(){
		return ['/post/category/index', 'slug' => $this->slug, 'lang' => $this->language];
	}

	/**
	 * @return array
	 */
	public function getPublicUrls(){
		return [
			'url'   => AppHelper::homeUrl() . Url::to($this->url),
			'label' => Yii::t('common', 'View on website'),
		];
	}
}
