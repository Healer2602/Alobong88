<?php

namespace modules\notification\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%notification_activity}}".
 *
 * @property int $id
 * @property string $title
 * @property string $receiver
 * @property string $type
 * @property string $content
 * @property string $trigger_name
 * @property string $created_by
 * @property int $created_at
 */
class NotificationActivity extends ActiveRecord{

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%notification_activity}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['content'], 'string'],
			[['created_at'], 'integer'],
			[['title'], 'string', 'max' => 1000],
			[['type', 'trigger_name', 'created_by', 'receiver'], 'string', 'max' => 255],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'           => Yii::t('common', 'ID'),
			'title'        => Yii::t('common', 'Title'),
			'type'         => Yii::t('common', 'Type'),
			'content'      => Yii::t('common', 'Content'),
			'trigger_name' => Yii::t('common', 'Trigger Name'),
			'created_by'   => Yii::t('common', 'Created By'),
			'created_at'   => Yii::t('common', 'Sent At'),
			'receiver'     => Yii::t('common', 'Sent To'),
		];
	}

	/**
	 * @param bool $insert
	 *
	 * @return bool
	 */
	public function beforeSave($insert){
		$this->created_at = time();

		return parent::beforeSave($insert);
	}
}
