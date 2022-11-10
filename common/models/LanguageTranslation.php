<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%language_translation}}".
 *
 * @property int $id
 * @property string $object_id
 * @property int $item_id
 * @property string $language
 * @property string $language_id
 *
 * @property-read array $siblings
 * @property-read \common\models\LanguageTranslation $parent
 */
class LanguageTranslation extends ActiveRecord{

	public $items = [];
	public $translation_id;

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%language_translation}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['object_id', 'item_id', 'language_id', 'language'], 'required'],
			[['item_id'], 'integer'],
			[['object_id', 'language_id', 'language'], 'string', 'max' => 255],
			['translation_id', 'integer'],
			['translation_id', 'unique', 'targetAttribute' => ['object_id', 'language', 'language_id']]
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'             => Yii::t('common', 'ID'),
			'object_id'      => Yii::t('common', 'Object'),
			'item_id'        => Yii::t('common', 'Item ID'),
			'language_id'    => Yii::t('common', 'Language ID'),
			'translation_id' => Yii::t('common', 'This is a translation of'),
		];
	}

	private $_siblings = NULL;

	/**
	 * @return array
	 */
	public function getSiblings(){
		if ($this->_siblings === NULL){
			$this->_siblings = self::find()
			                       ->select(['item_id'])
			                       ->andWhere(['language_id' => $this->language_id])
			                       ->column();
		}

		return $this->_siblings;
	}

	/**
	 * @return \yii\db\ActiveQuery
	 * @throws \Throwable
	 */
	public function getParent(){
		return $this->hasOne(LanguageTranslation::class, ['language_id' => 'language_id'])
		            ->andOnCondition(['language' => Language::default()]);
	}

	public static function findByKey($key){
		$translations = self::find()->andWhere(['language_id' => $key])->asArray()->all();
		if (!empty($translations)){
			$ids = ArrayHelper::getColumn($translations, 'item_id');
			/**@var \common\models\BaseActiveRecord $model */
			$model = $translations[0]['object_id'] ?? NULL;

			if (!empty($model)){
				$objects = $model::find()
				                 ->default()
				                 ->andWhere(['id' => $ids])
				                 ->indexBy('language')
				                 ->all();

				if (!empty($objects)){
					return ArrayHelper::getColumn($objects, 'url');
				}
			}
		}

		return [];
	}
}
