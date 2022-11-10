<?php

namespace backend\models;

use backend\models\Staff as User;
use common\models\AuditTrail;
use Yii;
use yii\base\Model;

/**
 * Create User
 *
 * @property array $groups
 */
class UserForm extends Model{

	const SCENARIO_CREATE = 'create';

	const SCENARIO_UPDATE = 'update';

	const SCENARIO_CHANGE_PASSWORD = 'change_password';

	public $id;
	public $username;
	public $email;
	public $name;
	public $password;
	public $confirm_password;
	public $user_group_id;
	public $status;
	public $is_change_password = FALSE;

	/**
	 * @inheritdoc
	 */
	public function rules(){
		return [
			[['email', 'username'], 'trim'],
			['email', 'email'],
			['email', 'string', 'max' => 255],
			[
				'email',
				'unique',
				'targetClass' => '\backend\models\Staff',
				'message'     => Yii::t('common', 'This email address has already been taken.'),
				'on'          => self::SCENARIO_CREATE
			],
			[
				'username',
				'unique',
				'targetClass' => '\backend\models\Staff',
				'message'     => Yii::t('common', 'This username has already been taken.'),
				'on'          => self::SCENARIO_CREATE
			],
			[['password', 'confirm_password'], 'string', 'min' => 6],
			[['password', 'confirm_password'], 'required', 'on' => self::SCENARIO_CREATE],
			['confirm_password', 'compare', 'compareAttribute' => 'password'],
			['password', 'compare', 'compareAttribute' => 'confirm_password'],

			['user_group_id', 'safe'],
			[['name', 'username'], 'string'],
			[['username', 'user_group_id', 'email'], 'required'],
			['name', 'required'],

			[
				'email',
				'validateDuplicate',
				'message' => Yii::t('common', 'This email address has already been taken.'),
				'on'      => self::SCENARIO_UPDATE
			],
			[
				'username',
				'validateDuplicate',
				'message' => Yii::t('common', 'This username has already been taken.'),
				'on'      => self::SCENARIO_UPDATE
			],
		];
	}

	/**
	 * @return array
	 */
	public function scenarios(){
		$scenarios                                 = parent::scenarios();
		$scenarios[self::SCENARIO_CHANGE_PASSWORD] = ['password', 'confirm_password', 'name'];

		return $scenarios;
	}

	/**
	 * @return array
	 */
	public function attributeLabels(){
		return [
			'user_group_id' => Yii::t('common', 'Roles'),
		];
	}

	/**
	 * @param $attribute
	 * @param $params
	 * @param $validator
	 */
	public function validateDuplicate($attribute, $params, $validator){
		$user = User::find()
		            ->andWhere([$attribute => $this->$attribute])
		            ->andWhere(['<>', 'id', $this->id])
		            ->exists();
		if ($user){
			$this->addError($attribute, $validator->message);
		}
	}

	/**
	 * Signs user up.
	 *
	 * @return User|null the saved model or null if saving fails
	 * @throws \yii\base\Exception
	 */
	public function signup(){
		if (!$this->validate()){
			return NULL;
		}

		$user           = new Staff();
		$user->username = $this->username;
		$user->email    = $this->email;
		$user->name     = $this->name;
		$user->setPassword($this->password);
		$user->generateAuthKey();

		if ($user->save()){
			AuditTrail::log(Yii::t('common', 'Staff'),
				Yii::t('common', 'Create new {0} : {1}', [Yii::t('common', 'staff'), $user->name]),
				Yii::t('common', 'Staff'));

			$this->id = $user->id;
			$this->updateGroup();

			return $user;
		}

		return NULL;
	}

	/**
	 * @return null|User
	 * @throws \yii\base\Exception
	 */
	public function update(){

		if (!$this->validate()){
			return NULL;
		}

		if (empty($this->id)){
			return $this->signup();
		}

		if ($user = Staff::findOne($this->id)){
			$user->username = $this->username;
			$user->email    = $this->email;
			$user->name     = $this->name;

			if (!empty($this->password)){
				$user->setPassword($this->password);
				$user->generateAuthKey();
			}

			if ($user->save()){
				AuditTrail::log(Yii::t('common', 'Staff'),
					Yii::t('common', 'Update {0} : {1}', [Yii::t('common', 'staff'), $user->name]),
					Yii::t('common', 'Staff'));

				UserUserGroup::deleteAll(['user_id' => $this->id]);
				$this->updateGroup();

				return $user;
			}
		}

		return NULL;
	}

	/**
	 * @return bool|\backend\models\UserForm|null
	 * @return bool
	 * @throws \yii\base\Exception
	 *
	 */
	public function changepass(){
		if (!$this->validate()){
			return NULL;
		}

		if ($user = Staff::findOne($this->id)){
			$user->name = $this->name;

			if (!empty($this->password)){
				$user->setPassword($this->password);
				$user->generateAuthKey();
			}

			return $user->save();
		}

		return FALSE;
	}

	/**
	 * @return array
	 * @throws \Throwable
	 */
	public function getGroups(){
		return UserGroup::findList();
	}

	/**
	 * @return bool
	 */
	public function getIsNewRecord(){
		if (empty($this->id)){
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @throws \yii\db\Exception
	 */
	protected function updateGroup(){
		if (!empty($this->user_group_id)){
			$user_groups = [];
			foreach ($this->user_group_id as $group_id){
				$user_groups[] = new UserUserGroup([
					'user_id'       => $this->id,
					'user_group_id' => $group_id
				]);
			}
			if (UserUserGroup::validateMultiple($user_groups)){
				Yii::$app->db->createCommand()
				             ->batchInsert(UserUserGroup::tableName(),
					             ['user_id', 'user_group_id'], $user_groups)
				             ->execute();
			}
		}
	}
}
