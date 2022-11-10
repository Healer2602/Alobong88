<?php

namespace common\models;

use backend\models\Staff;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%audit_trail}}".
 *
 * @property int $id
 * @property int $user_id
 * @property string $user_name
 * @property int $created_at
 * @property string $action
 * @property string $system
 * @property string $message
 * @property string $module
 * @property string $ip_address
 * @property string $status
 *
 * @property Staff $admin
 * @property string $systemLabel
 */
class AuditTrail extends ActiveRecord{

	const SYSTEM_CMS = 'admin';

	const SYSTEM_FE = 'website';

	const SYSTEM_CONSOLE = 'console';

	const SYSTEM_API = 'api';

	const STATUS_SUCCESS = 'success';

	const STATUS_FAILED = 'failed';

	/**
	 * {@inheritdoc}
	 */
	public static function tableName(){
		return '{{%audit_trail}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['user_id', 'created_at'], 'integer'],
			[['action', 'module'], 'required'],
			[['message', 'ip_address', 'action', 'user_name'], 'string'],
			[['action', 'system', 'module', 'link', 'status'], 'string', 'max' => 255],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels(){
		return [
			'id'         => Yii::t('common', 'ID'),
			'user_id'    => Yii::t('common', 'User'),
			'user_name'  => Yii::t('common', 'User Name'),
			'created_at' => Yii::t('common', 'Created At'),
			'action'     => Yii::t('common', 'Action'),
			'module'     => Yii::t('common', 'Module'),
			'message'    => Yii::t('common', 'Message'),
			'system'     => Yii::t('common', 'System'),
			'status'     => Yii::t('common', 'Status'),
			'ip_address' => Yii::t('common', 'IP Address'),
		];
	}

	/**
	 * @param bool $insert
	 *
	 * @return bool
	 */
	public function beforeSave($insert){
		if ($insert){
			$this->user_name = 'SYSTEM';

			if (empty($this->user_id) && !empty(Yii::$app->user)){
				$this->user_id   = Yii::$app->user->id;
				$this->user_name = Yii::$app->user->identity->name ?? 'SYSTEM';
			}

			if (!empty(Yii::$app->request->userIP)){
				$this->ip_address = Yii::$app->request->userIP;
			}

			$this->created_at = time();
		}

		return parent::beforeSave($insert);
	}

	/**
	 * @param $action
	 * @param $message
	 * @param null $module
	 * @param null $user_id
	 * @param string $status
	 * @param null $system
	 *
	 * @return bool
	 */
	public static function log(
		$action,
		$message,
		$module = NULL,
		$user_id = NULL,
		$status = "success",
		$system = NULL){
		$audit          = new AuditTrail();
		$audit->action  = $action;
		$audit->message = $message;
		$audit->module  = $module ?? 'System';
		$audit->system  = $system ?? Yii::$app->id;
		$audit->status  = $status;
		$audit->user_id = $user_id;

		return $audit->save(FALSE);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getAuthor(){
		return $this->hasOne(Staff::class, ['id' => 'user_id']);
	}

	/**
	 * @return array
	 * @throws \Throwable
	 */
	public static function getSystems(){
		return [
			AuditTrail::SYSTEM_CMS     => Yii::t('common', 'Admin Site'),
			AuditTrail::SYSTEM_FE      => Yii::t('common', 'Public Site'),
			AuditTrail::SYSTEM_CONSOLE => Yii::t('common', 'Cronjob'),
			AuditTrail::SYSTEM_API     => Yii::t('common', 'API'),
		];
	}

	/**
	 * @return string
	 * @throws \Throwable
	 */
	public function getSystemLabel(){
		$systems = self::getSystems();

		return $systems[$this->system] ?? NULL;
	}
}
