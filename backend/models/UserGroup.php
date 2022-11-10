<?php

namespace backend\models;

use common\base\AuditTrailBehavior;
use common\models\BaseActiveRecord;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%user_group}}".
 *
 * @property int $id
 * @property string $name
 * @property int $status
 * @property int $is_primary
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 *
 * @property UserGroupPermission[] $permissions
 * @property UserUserGroup[] $userUserGroups
 * @property \backend\models\Staff[] $users
 */
class UserGroup extends BaseActiveRecord{

	const SCENARIO_STATUS = 'status';

	public static $alias = 'user_group';

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%user_group}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['name'], 'required'],
			[['status', 'is_primary', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
			[['name'], 'string', 'max' => 255],
		];
	}

	/**
	 * @return array
	 */
	public function behaviors(){
		return [
			BlameableBehavior::class,
			TimestampBehavior::class,
			[
				'class'  => AuditTrailBehavior::class,
				'module' => Yii::t('common', 'Roles')
			]
		];
	}

	/**
	 * @return array|array[]
	 */
	public function scenarios(){
		$scenarios                        = parent::scenarios();
		$scenarios[self::SCENARIO_STATUS] = ['status'];

		return $scenarios;
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'         => Yii::t('common', 'ID'),
			'name'       => Yii::t('common', 'Name'),
			'status'     => Yii::t('common', 'Status'),
			'is_primary' => Yii::t('common', 'Is Primary'),
			'created_by' => Yii::t('common', 'Created By'),
			'created_at' => Yii::t('common', 'Created At'),
			'updated_by' => Yii::t('common', 'Updated By'),
			'updated_at' => Yii::t('common', 'Updated At'),
		];
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getPermissions(){
		return $this->hasMany(UserGroupPermission::class, ['user_group_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getUserUserGroups(){
		return $this->hasMany(UserUserGroup::class, ['user_group_id' => 'id']);
	}


	/**
	 * @return \yii\db\ActiveQuery
	 * @throws \yii\base\InvalidConfigException
	 */
	public function getUsers(){
		return $this->hasMany(Staff::class, ['id' => 'user_id'])
		            ->viaTable(UserUserGroup::tableName(), ['user_group_id' => 'id']);
	}
}
