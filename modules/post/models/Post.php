<?php

namespace modules\post\models;

use backend\models\Staff;
use common\base\AppHelper;
use common\base\Status;
use common\models\BaseActiveRecord;
use Yii;
use yii\behaviors\SluggableBehavior;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * This is the model class for table "{{%post}}".
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $intro
 * @property string $content
 * @property string $thumbnail
 * @property int $category_id
 * @property int $ordering
 * @property int $status
 * @property string $type
 * @property string $position
 * @property string $language
 * @property string|array $tags
 * @property string|array $related_tags
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 *
 * @property Category $category
 * @property array $statuses
 * @property array $categories
 * @property Staff $author
 * @property string $mediaPath
 * @property array $url
 * @property \modules\post\models\Post[] $relates
 * @property string $typeLabel
 */
class Post extends BaseActiveRecord{

	const SCENARIO_UPSERT = 'upsert';

	public static $alias = 'post';

	public static $post_type = 'post';

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%post}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['name'], 'required'],
			[['intro', 'language', 'type', 'thumbnail', 'position'], 'string'],
			[['category_id', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at', 'ordering'], 'integer'],
			[['name', 'slug'], 'string', 'max' => 255],
			[['slug', 'type'], 'unique', 'targetAttribute' => ['slug', 'type']],
			[['intro'], 'string', 'max' => 2000],
			[['tags', 'related_tags', 'content'], 'safe'],
			['ordering', 'default', 'value' => 1]
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'           => Yii::t('common', 'ID'),
			'slug'         => Yii::t('common', 'Slug'),
			'intro'        => Yii::t('common', 'Intro'),
			'content'      => Yii::t('common', 'Content'),
			'status'       => Yii::t('common', 'Status'),
			'thumbnail'    => Yii::t('common', 'Thumbnail'),
			'tags'         => Yii::t('common', 'Tags'),
			'related_tags' => Yii::t('common', 'Related Tags'),
			'language'     => Yii::t('common', 'Language'),

			'created_by'  => Yii::t('post', 'Author'),
			'created_at'  => Yii::t('post', 'Created at'),
			'updated_by'  => Yii::t('post', 'Published By'),
			'updated_at'  => Yii::t('post', 'Published At'),
			'name'        => Yii::t('post', 'Title'),
			'category_id' => Yii::t('post', 'Category'),
		];
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

		$behaviors['audit']['module'] = Yii::t('post', 'Post');
		unset($behaviors['status']);

		return $behaviors;
	}

	/**
	 * @return array
	 */
	public function scenarios(){
		$scenarios                        = parent::scenarios();
		$scenarios[self::SCENARIO_UPSERT] = $scenarios[self::SCENARIO_DEFAULT];

		return $scenarios;
	}

	/**
	 * @return \common\base\ActiveQuery
	 */
	public static function find(){
		return parent::find()->andWhere([static::$alias . '.type' => static::$post_type]);
	}

	/**
	 * @param bool $insert
	 *
	 * @return bool
	 */
	public function beforeSave($insert){
		$this->type = static::$post_type;

		if (!empty($this->slug)){
			$this->slug = Inflector::slug($this->slug);
			$this->detachBehavior('slug');
		}

		if (!empty($this->tags) && is_array($this->tags)){
			$this->tags = ArrayHelper::map(array_filter($this->tags), function ($data){
				return Inflector::slug($data);
			}, function ($data){
				return $data;
			});

			$this->tags = Json::encode($this->tags);
		}elseif (empty($this->tags)){
			$this->tags = Json::encode([]);
		}

		if (!empty($this->related_tags) && is_array($this->related_tags)){
			$this->related_tags = array_filter($this->related_tags);
			$this->related_tags = Json::encode($this->related_tags);
		}elseif (empty($this->related_tags)){
			$this->related_tags = Json::encode([]);
		}

		return parent::beforeSave($insert);
	}

	/**
	 * @inheritDoc
	 */
	public function afterFind(){
		if (!empty($this->tags)){
			$this->tags = Json::decode($this->tags);
			$this->tags = ArrayHelper::map($this->tags, function ($data){
				return $data;
			}, function ($data){
				return $data;
			});
		}else{
			$this->tags = [];
		}

		if (!empty($this->related_tags)){
			$this->related_tags = Json::decode($this->related_tags);
			$this->related_tags = ArrayHelper::map($this->related_tags, function ($data){
				return $data;
			}, function ($data){
				return $data;
			});
		}else{
			$this->related_tags = [];
		}

		parent::afterFind();
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getCategory(){
		return $this->hasOne(Category::class, ['id' => 'category_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getAuthor(){
		return $this->hasOne(Staff::class, ['id' => 'created_by']);
	}

	/**
	 * @return string
	 */
	public function getTypeLabel(){
		$label = Inflector::humanize($this->type);

		return Yii::t('post', $label);
	}

	/**
	 * @return array
	 */
	public function getStatuses(){
		$statuses = Status::states();
		unset($statuses[Status::STATUS_ALL]);

		return $statuses;
	}

	/**
	 * @return array
	 * @throws \Throwable
	 */
	public function getCategories(){
		return Category::findList();
	}

	/**
	 * @return string
	 */
	public function getMediaPath(){
		return 'post/' . static::$post_type;
	}

	/**
	 * @return array
	 */
	public function getUrl(){
		return ["/post/{$this->type}/index", 'slug' => $this->slug, 'type' => $this->type, 'lang' => $this->language];
	}

	/**
	 * @return \modules\post\models\Post[]
	 */
	public function getRelates(){
		$query = static::find()->with('category')
		               ->orderBy(['created_at' => SORT_DESC])
		               ->andWhere(['<>', 'id', $this->id])
		               ->andWhere(['type' => $this->type])
		               ->translate()
		               ->limit(5);

		$related_query = clone $query;

		if (!$tags = $this->related_tags){
			$tag_args = [];
			foreach ($tags as $tag){
				$tag_args[] = ['LIKE', 'related_tags', "\"$tag\""];
			}

			if ($tag_args){
				$query->andWhere(['OR'] + $tag_args);
			}
		}

		if (!$query->count()){
			$related_query->andWhere(['category_id' => $this->category_id]);

			return $related_query->all();
		}

		return $query->all();
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
