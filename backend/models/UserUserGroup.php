<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%user_user_group}}".
 *
 * @property int $user_id
 * @property int $user_group_id
 *
 * @property UserGroup $userGroup
 * @property \backend\models\Staff $user
 */
class UserUserGroup extends ActiveRecord{

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%user_user_group}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['user_id', 'user_group_id'], 'required'],
			[['user_id', 'user_group_id'], 'integer'],
			[['user_id', 'user_group_id'], 'unique', 'targetAttribute' => ['user_id', 'user_group_id']],
			[['user_group_id'], 'exist', 'skipOnError' => TRUE, 'targetClass' => UserGroup::class, 'targetAttribute' => ['user_group_id' => 'id']],
			[['user_id'], 'exist', 'skipOnError' => TRUE, 'targetClass' => Staff::class, 'targetAttribute' => ['user_id' => 'id']],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'user_id'       => Yii::t('common', 'User ID'),
			'user_group_id' => Yii::t('common', 'User Group ID'),
		];
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getUserGroup(){
		return $this->hasOne(UserGroup::class, ['id' => 'user_group_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getUser(){
		return $this->hasOne(Staff::class, ['id' => 'user_id']);
	}
}
