<?php

namespace modules\media_center\models;

use backend\models\Staff;
use common\models\BaseActiveRecord;
use modules\media_center\base\ImportHelper;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%import_log}}".
 *
 * @property int $id
 * @property string $importer
 * @property string $validator
 * @property string $import_class
 * @property int $status
 * @property string|array $description
 * @property string $filename
 * @property string $error_log
 * @property int $created_at
 * @property int $created_by
 * @property int $updated_at
 * @property int $completed_at
 *
 * @property-read Staff $author
 */
class ImportLog extends BaseActiveRecord{

	const STATUS_PAUSED = 0;

	const STATUS_PENDING = 10;

	const STATUS_IN_PROGRESS = 20;

	const STATUS_RETRY = 25;

	const STATUS_DONE = 30;

	const STATUS_ERROR = - 10;

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%import_log}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['importer'], 'required'],
			[['status', 'created_at', 'created_by', 'updated_at', 'completed_at'], 'integer'],
			[['error_log', 'filename'], 'string'],
			[['importer'], 'string', 'max' => 255],
			[['validator', 'import_class'], 'string', 'max' => 500],
			['description', 'safe'],
			['status', 'default', 'value' => self::STATUS_PENDING]
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'           => Yii::t('media_center', 'ID'),
			'importer'     => Yii::t('media_center', 'Importer'),
			'validator'    => Yii::t('media_center', 'Validator'),
			'import_class' => Yii::t('media_center', 'Import Class'),
			'status'       => Yii::t('media_center', 'Status'),
			'description'  => Yii::t('media_center', 'Description'),
			'created_at'   => Yii::t('media_center', 'Created At'),
			'created_by'   => Yii::t('media_center', 'Created By'),
			'updated_at'   => Yii::t('media_center', 'Updated At'),
			'completed_at' => Yii::t('media_center', 'Completed At'),
		];
	}

	/**
	 * @return string[]
	 */
	public function behaviors(){
		return [
			TimestampBehavior::class,
		];
	}

	/**
	 * @param $insert
	 *
	 * @return bool
	 */
	public function beforeSave($insert){
		if (is_array($this->description)){
			$this->description = Json::encode($this->description);
		}

		if ($insert){
			$this->validator    = ImportHelper::validator($this->importer);
			$this->import_class = ImportHelper::importClass($this->importer);
			$this->created_at   = time();
			$this->created_by   = Yii::$app->user->id;
		}else{
			$this->updated_at = time();
		}

		return parent::beforeSave($insert);
	}

	/**
	 * @return void
	 */
	public function afterFind(){
		if (!empty($this->description) && is_string($this->description)){
			$this->description = Json::decode($this->description);
		}

		if (empty($this->description)){
			$this->description = [];
		}

		parent::afterFind();
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getAuthor(){
		return $this->hasOne(Staff::class, ['id' => 'created_by']);
	}

	/**
	 * @return bool
	 */
	public function isOwner(){
		return $this->created_by == Yii::$app->user->id;
	}

	/**
	 * @return bool
	 */
	public function canDelete(){
		return $this->isOwner() && ArrayHelper::isIn($this->status,
				[self::STATUS_PENDING, self::STATUS_PAUSED]);
	}

	/**
	 * @return bool
	 */
	public function canPause(){
		return $this->isOwner() && $this->status == self::STATUS_PENDING;
	}

	/**
	 * @return bool
	 */
	public function canResume(){
		return $this->isOwner() && $this->status == self::STATUS_PAUSED;
	}

	/**
	 * @return bool
	 */
	public function canRemove(){
		return $this->isOwner() && $this->status == self::STATUS_IN_PROGRESS;
	}

	/**
	 * @return mixed|string
	 */
	public function getName(){
		return ImportHelper::list()[$this->importer] ?? $this->importer;
	}

	/**
	 * @return array
	 */
	public static function statuses()
	: array{
		return [
			self::STATUS_PAUSED      => Yii::t('media_center', 'Paused'),
			self::STATUS_PENDING     => Yii::t('media_center', 'Pending'),
			self::STATUS_IN_PROGRESS => Yii::t('media_center', 'In Progress'),
			self::STATUS_RETRY       => Yii::t('media_center', 'Retrying'),
			self::STATUS_DONE        => Yii::t('media_center', 'Done'),
			self::STATUS_ERROR       => Yii::t('media_center', 'Error'),
		];
	}

	/**
	 * @return mixed|null
	 */
	public function getStatusLabel(){
		return self::statuses()[$this->status] ?? NULL;
	}
}