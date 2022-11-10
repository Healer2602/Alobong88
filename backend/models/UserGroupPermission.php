<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%user_group_permission}}".
 *
 * @property int $user_group_id
 * @property int $user_permission_id
 *
 * @property UserPermission $userPermission
 * @property UserGroup $userGroup
 */
class UserGroupPermission extends ActiveRecord{

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%user_group_permission}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['user_group_id', 'user_permission_id'], 'required'],
			[['user_group_id', 'user_permission_id'], 'integer'],
			[['user_permission_id'], 'exist', 'skipOnError' => TRUE, 'targetClass' => UserPermission::class, 'targetAttribute' => ['user_permission_id' => 'id']],
			[['user_group_id'], 'exist', 'skipOnError' => TRUE, 'targetClass' => UserGroup::class, 'targetAttribute' => ['user_group_id' => 'id']],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'user_group_id'      => Yii::t('common', 'User Group ID'),
			'user_permission_id' => Yii::t('common', 'User Permission ID'),
		];
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getUserPermission(){
		return $this->hasOne(UserPermission::class, ['id' => 'user_permission_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getUserGroup(){
		return $this->hasOne(UserGroup::class, ['id' => 'user_group_id']);
	}

	/**
	 * @param array $data
	 *
	 * @return bool|int
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\db\Exception
	 */
	public static function upsert($data = []){
		$attributes = UserGroupPermission::getTableSchema()->getColumnNames();

		if (self::validateMultiple($data, $attributes)){
			$group_id = ArrayHelper::getColumn($data, 'user_group_id');
			self::deleteAll(['user_group_id' => $group_id]);
			Yii::$app->authManager->clearCache($group_id);

			return Yii::$app->db->createCommand()
			                    ->batchInsert(self::tableName(), $attributes, $data)->execute();
		}

		return FALSE;
	}
}
