<?php

namespace modules\block\models;

use common\base\Status;
use common\models\BaseActiveRecord;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%block}}".
 *
 * @property int $id
 * @property string $name
 * @property string $title
 * @property string $content
 * @property string $image
 * @property string|array $setting
 * @property string $type
 * @property string $position
 * @property string $language
 * @property int $ordering
 * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 *
 * @property array $types
 * @property array $positions
 * @property string $positionLabel
 * @property string $typeLabel
 */
class Block extends BaseActiveRecord{

	/**
	 * @var string
	 */
	public static $alias = 'block';

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%block}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['name'], 'required'],
			[['content', 'language'], 'string'],
			[['setting', 'image'], 'safe'],
			[['ordering', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
			[['name', 'title', 'type', 'position'], 'string', 'max' => 255],
			['ordering', 'default', 'value' => 1]
		];
	}

	/**
	 * @return array
	 */
	public function behaviors(){
		$behaviors                    = parent::behaviors();
		$behaviors['audit']['module'] = Yii::t('block', 'Block');

		return $behaviors;
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'         => Yii::t('common', 'ID'),
			'name'       => Yii::t('common', 'Name'),
			'title'      => Yii::t('common', 'Title'),
			'content'    => Yii::t('common', 'Content'),
			'language'   => Yii::t('common', 'Language'),
			'status'     => Yii::t('common', 'Status'),
			'created_by' => Yii::t('common', 'Created By'),
			'created_at' => Yii::t('common', 'Created At'),
			'updated_by' => Yii::t('common', 'Updated By'),
			'updated_at' => Yii::t('common', 'Updated At'),

			'setting'  => Yii::t('block', 'Settings'),
			'type'     => Yii::t('block', 'Type'),
			'ordering' => Yii::t('block', 'Ordering'),
			'position' => Yii::t('block', 'Position'),
		];
	}

	/**
	 * @param bool $insert
	 *
	 * @return bool
	 */
	public function beforeSave($insert){
		if (is_array($this->setting)){
			$this->setting = Json::encode($this->setting);
		}

		return parent::beforeSave($insert);
	}

	/**
	 * @param bool $insert
	 * @param array $changedAttributes
	 */
	public function afterSave($insert, $changedAttributes){
		if ($insert){
			Block::updateAllCounters(['ordering' => 1], "id <> :id", [':id' => $this->id]);
		}

		parent::afterSave($insert, $changedAttributes);
	}

	/**
	 * @inheritDoc
	 */
	public function afterFind(){
		if (is_string($this->setting)){
			$this->setting = Json::decode($this->setting);
		}

		parent::afterFind();
	}

	/**
	 * @return array
	 */
	public function getTypes(){
		$module_widgets = Yii::$app->params['block.types'] ?? [];

		return ArrayHelper::getColumn($module_widgets, function ($data){
			if (is_string($data)){
				return $data;
			}

			return $data['name'] ?? NULL;
		});
	}

	/**
	 * @return array
	 */
	public function getPositions(){
		return Yii::$app->params['block.positions'] ?? [];
	}

	/**
	 * @return mixed|null
	 */
	public function getTypeLabel(){
		return $this->types[$this->type] ?? NULL;
	}

	/**
	 * @return mixed|null
	 */
	public function getPositionLabel(){
		return $this->positions[$this->position] ?? NULL;
	}

	/**
	 * @param string $position
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public static function findByPosition($position){
		return self::find()
		           ->translate()
		           ->andWhere(['status' => Status::STATUS_ACTIVE, 'position' => $position])
		           ->orderBy(['ordering' => SORT_ASC]);
	}

	/**
	 * @return \modules\block\models\Block|null
	 */
	public function formObject(){
		$class = Yii::$app->params['block.types'][$this->type]['class'] ?? NULL;
		if (!empty($class) && class_exists($class)){
			return new $class;
		}

		return NULL;
	}
}
