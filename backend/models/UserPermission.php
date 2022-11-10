<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%user_permission}}".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $created_at
 * @property int $updated_at
 * @property int $synced
 *
 * @property UserGroupPermission[] $userGroupPermissions
 */
class UserPermission extends ActiveRecord{

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%user_permission}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['name'], 'required'],
			[['created_at', 'updated_at', 'synced'], 'integer'],
			[['name', 'description'], 'string', 'max' => 255],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'          => Yii::t('common', 'ID'),
			'name'        => Yii::t('common', 'Name'),
			'description' => Yii::t('common', 'Description'),
			'created_at'  => Yii::t('common', 'Created At'),
			'updated_at'  => Yii::t('common', 'Updated At'),
			'synced'      => Yii::t('common', 'Synced'),
		];
	}

	/**
	 * @param bool $insert
	 *
	 * @return bool
	 */
	public function beforeSave($insert){
		if ($insert){
			$this->created_at = time();
		}

		$this->updated_at = time();

		return parent::beforeSave($insert);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getUserGroupPermissions(){
		return $this->hasMany(UserGroupPermission::class, ['user_permission_id' => 'id']);
	}
}
