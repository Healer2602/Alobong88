<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%user_language}}".
 *
 * @property int $user_id
 * @property string $language
 */
class UserLanguage extends BaseActiveRecord{

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%user_language}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['user_id'], 'integer'],
			[['language'], 'required'],
			[['language'], 'string', 'max' => 2],
			[['user_id'], 'unique'],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'user_id'  => Yii::t('common', 'User ID'),
			'language' => Yii::t('common', 'Language'),
		];
	}

	/**
	 * @return array
	 */
	public function behaviors(){
		$behaviors = parent::behaviors();
		unset($behaviors['status'], $behaviors['language']);

		return $behaviors;
	}
}