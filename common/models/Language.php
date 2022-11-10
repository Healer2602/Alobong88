<?php

namespace common\models;

use backend\models\Staff;
use common\base\Status;
use Symfony\Component\Yaml\Yaml;
use Yii;
use yii\bootstrap5\Html;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;
use yii\web\User;

/**
 * This is the model class for table "{{%language}}".
 *
 * @property int $id
 * @property string $name
 * @property string $key
 * @property int $is_default
 * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 *
 * @property-read array $languages
 * @property-read array $statuses
 * @property-read string $systemKey
 * @property User $author
 */
class Language extends BaseActiveRecord{

	const DEFAULT_LANGUAGE = 1;

	const SESSION_KEY = 'language.current';

	public static $alias = 'language';

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%language}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['name', 'key'], 'required'],
			[['is_default', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
			[['name', 'key'], 'string', 'max' => 255],
			[['key'], 'unique'],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'name'       => Yii::t('common', 'Name'),
			'key'        => Yii::t('common', 'Source Language'),
			'is_default' => Yii::t('common', 'Default Language'),
			'status'     => Yii::t('common', 'Status'),
			'created_by' => Yii::t('common', 'Author'),
			'created_at' => Yii::t('common', 'Created at'),
			'updated_by' => Yii::t('common', 'Updated By'),
			'updated_at' => Yii::t('common', 'Updated At'),
		];
	}

	/**
	 * @return array
	 */
	public function behaviors(){
		$behaviors = parent::behaviors();
		unset($behaviors['status'], $behaviors['language']);
		$behaviors['audit']['module']   = Yii::t('common', 'System');
		$behaviors['audit']['category'] = Yii::t('common', 'Language');

		return $behaviors;
	}

	/**
	 * @return array|mixed
	 */
	public function getConfigs(){
		$app_path = Yii::getAlias("@common/language.yml");

		return Yaml::parseFile($app_path);
	}

	/**
	 * @return array
	 */
	public function getLanguages(){
		$languages = $this->getConfigs();

		if (!empty($languages)){
			return ArrayHelper::getColumn($languages, 'name', TRUE);
		}

		return [];
	}

	/**
	 * @return array
	 */
	public function getSourceLanguages(){
		$languages = $this->getConfigs();

		if (!empty($languages)){
			return ArrayHelper::getColumn($languages, 'source', TRUE);
		}

		return [];
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getAuthor(){
		return $this->hasOne(Staff::class, ['id' => 'created_by']);
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
	 * @param bool $insert
	 *
	 * @return bool
	 */
	public function beforeSave($insert){
		if ($this->is_default){
			$this->status = Status::STATUS_ACTIVE;
		}

		return parent::beforeSave($insert);
	}

	/**
	 * @param bool $insert
	 * @param array $changedAttributes
	 */
	public function afterSave($insert, $changedAttributes){
		if ($this->is_default){
			$condition = ['and', ['is_default' => self::DEFAULT_LANGUAGE], ['!=', 'id', $this->id]];
			Language::updateAll(['is_default' => 0], $condition);
		}

		TagDependency::invalidate(Yii::$app->cache, [
			self::SESSION_KEY . '.list',
			self::SESSION_KEY . '.default'
		]);

		parent::afterSave($insert, $changedAttributes);
	}

	/**
	 * @return mixed|string
	 */
	public function getSystemKey(){
		if (!empty($this->languages[$this->key])){
			return $this->languages[$this->key] . Html::tag('span', $this->key,
					['class' => 'badge badge-soft-info ml-2']);
		}

		return $this->key;
	}

	/**
	 * @param bool $all
	 *
	 * @return array
	 * @throws \Throwable
	 */
	public static function listLanguage($all = TRUE){
		$languages = self::getDb()->cache(function (){
			return self::find()->orderBy(['name' => SORT_ASC])->asArray()->all();
		}, 0, new TagDependency([
			'tags' => self::SESSION_KEY . '.list'
		]));

		if (!empty($languages)){
			if (empty($all)){
				foreach ($languages as $id => &$language){
					if (empty($language['status'])){
						unset($languages[$id]);
					}
				}
			}

			return ArrayHelper::map($languages, 'key', 'name');
		}

		return [];
	}

	/**
	 * @return mixed|null
	 * @throws \Throwable
	 */
	public static function current(){
		if (Yii::$app->has('session')){
			return Yii::$app->session->get(static::SESSION_KEY, self::default());
		}

		return self::default();
	}

	/**
	 * @return mixed|null
	 * @throws \Throwable
	 */
	public static function currentName(){
		$current = self::current();

		return self::listLanguage(FALSE)[$current] ?? '';
	}

	/**
	 * @return mixed|null
	 * @throws \Throwable
	 */
	public static function currentSource(){
		$sources = (new static())->getSourceLanguages();

		return $sources[static::current()] ?? static::current();
	}

	/**
	 * @param null|string $language
	 *
	 * @return bool
	 * @throws \yii\db\Exception
	 */
	public static function setDefault($language = NULL){
		if (empty($language)){
			Yii::$app->session->remove(static::SESSION_KEY);

			return TRUE;
		}

		if (self::find()
		        ->andWhere(['key' => $language, 'status' => Status::STATUS_ACTIVE])
		        ->exists()){
			Yii::$app->session->set(static::SESSION_KEY, $language);

			self::storeLang($language);

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @param $language
	 *
	 * @return int
	 * @throws \yii\db\Exception
	 */
	private static function storeLang($language){
		$user = Yii::$app->user->identity->id ?? NULL;

		if (!empty($user)){
			return Yii::$app->db->createCommand()->upsert(UserLanguage::tableName(), [
				'user_id'  => $user,
				'language' => $language,
			], [
				'language' => $language
			])->execute();
		}

		return NULL;
	}

	/**
	 * @return mixed|string
	 * @throws \Throwable
	 */
	public static function default(){
		$default = self::getDb()->cache(function (){
			return self::find()
			           ->select(['key'])
			           ->default()
			           ->andWhere(['is_default' => self::DEFAULT_LANGUAGE])
			           ->scalar();
		}, 0, new TagDependency([
			'tags' => self::SESSION_KEY . '.default'
		]));

		if (empty($default)){
			return Yii::$app->params['language.default'] ?? Yii::$app->language;
		}

		return $default;
	}

	/**
	 * @return false|string
	 */
	public static function app(){
		$language = Yii::$app->language;
		if ($language == 'zh-CN'){
			$custom_language = 'cn';
		}elseif ($language == 'zh-TW'){
			$custom_language = 'tw';
		}else{
			$custom_language = $language;
		}

		return $custom_language;
	}
}
