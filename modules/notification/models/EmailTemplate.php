<?php

namespace modules\notification\models;

use common\base\Status;
use common\models\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%email_template}}".
 *
 * @property int $id
 * @property string $trigger_key
 * @property string $name
 * @property string $subject
 * @property string $language
 * @property string $content
 * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 *
 * @property Trigger $appTrigger
 * @property array $triggers
 * @property array $params
 */
class EmailTemplate extends BaseActiveRecord{

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%email_template}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['name', 'subject'], 'required'],
			[['content'], 'string'],
			[['status', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
			[['trigger_key', 'language'], 'string', 'max' => 255],
			[['name', 'subject'], 'string', 'max' => 1000],
			[['trigger_key', 'language'], 'unique', 'targetAttribute' => ['trigger_key', 'language']],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'          => Yii::t('common', 'ID'),
			'trigger_key' => Yii::t('common', 'Trigger'),
			'name'        => Yii::t('common', 'Name'),
			'subject'     => Yii::t('common', 'Subject'),
			'language'    => Yii::t('common', 'Language'),
			'content'     => Yii::t('common', 'Content'),
			'status'      => Yii::t('common', 'Status'),
			'created_by'  => Yii::t('common', 'Created By'),
			'created_at'  => Yii::t('common', 'Created At'),
			'updated_by'  => Yii::t('common', 'Updated By'),
			'updated_at'  => Yii::t('common', 'Updated At'),
		];
	}

	/**
	 * @return array
	 */
	public function attributeHints(){
		if (!empty($this->appTrigger->templateParams)){
			return [
				'content' => Yii::t('common', 'Available variables: {0}',
					[implode(', ', $this->appTrigger->templateParams)]),
			];
		}

		return [];
	}

	/**
	 * @param string $key
	 *
	 * @return EmailTemplate
	 */
	public static function findKey($key, $language = ''){
		if (empty($language)){
			$language = 'vi';
		}

		$email = self::findOne(['trigger_key' => $key, 'status' => Status::STATUS_ACTIVE, 'language' => $language]);
		if (empty($email)){
			$email = self::find()
			             ->andWhere(['trigger_key' => $key, 'status' => Status::STATUS_ACTIVE, 'language' => ['vi', 'en']])
			             ->orderBy(['language' => SORT_ASC])
			             ->one();
		}

		return $email;
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getAppTrigger(){
		return $this->hasOne(Trigger::class, ['key' => 'trigger_key']);
	}

	/**
	 * @return array
	 */
	public function getTriggers(){
		return Trigger::findList();
	}

	/**
	 * @return array
	 */
	public function getParams(){
		return $this->appTrigger->emailParams ?? [];
	}
}