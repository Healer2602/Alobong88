<?php

namespace backend\models;

use common\base\AppHelper;
use common\models\AuditTrail;
use Yii;
use yii\base\Model;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;

/**
 * Search Model for Audit Trail
 *
 * {@inheritdoc}
 * @property-read array $statuses
 * @property-read array $modules
 * @property-read array $systems
 * @property-read array $users
 */
class AuditTrailSearchModel extends Model{

	public $system;
	public $user;
	public $module;
	public $from;
	public $to;
	public $keywords;
	public $ip;

	/**
	 * @return array
	 */
	public function attributeLabels(){
		return [
			'keywords' => Yii::t('common', 'Action/Message'),
			'module'   => Yii::t('common', 'Module'),
			'system'   => Yii::t('common', 'System'),
			'from'     => Yii::t('common', 'Start Date'),
			'to'       => Yii::t('common', 'End Date'),
			'user'     => Yii::t('common', 'User'),
			'ip'       => Yii::t('common', 'IP Address'),
		];
	}

	/**
	 * @param array $filtering
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public static function findModels($filtering = []){
		$query = AuditTrail::find()->with('author');

		if (!empty($filtering['user'])){
			if ($filtering['user'] == - 1){
				$query->andWhere(['user_id' => NULL]);
			}else{
				$query->andWhere(['user_id' => $filtering['user']]);
			}
		}

		if (!empty($filtering['keywords'])){
			$query->andFilterWhere(['OR', ['LIKE', 'message', $filtering['keywords']], ['LIKE', 'action', $filtering['keywords']]]);
		}

		$query->andFilterWhere(['module' => $filtering['module'] ?? NULL]);
		$query->andFilterWhere(['system' => $filtering['system'] ?? NULL]);

		if (!empty($filtering['from'])){
			$query->andFilterCompare('created_at', AppHelper::parseDatetime($filtering['from']),
				'>=');
		}

		if (!empty($filtering['to'])){
			$query->andFilterCompare('created_at', AppHelper::parseDatetime($filtering['to']),
				'<=');
		}

		return $query;

	}

	/**
	 * @return array
	 * @throws \Throwable
	 */
	public function getUsers(){
		$data = AuditTrail::getDb()->cache(function (){
			return AuditTrail::find()
			                 ->joinWith('author author', FALSE)
			                 ->select(['user_id', 'user_name', 'author.name'])
			                 ->limit(- 1)->distinct()->asArray()->all();
		}, 0, new TagDependency([
			'tags' => "AUDIT_TRAIL_USERS"
		]));

		return ArrayHelper::map($data, 'user_id', function ($data){
			return $data['name'] ?? $data['user_name'] ?? 'SYSTEM';
		});
	}

	/**
	 * @return array
	 * @throws \Throwable
	 */
	public function getModules(){
		return AuditTrail::getDb()->cache(function (){
			return AuditTrail::find()
			                 ->select('module')
			                 ->indexBy('module')
			                 ->distinct()
			                 ->asArray()
			                 ->column();
		}, 0, new TagDependency([
			'tags' => 'AUDIT_TRAIL_MODULES'
		]));
	}

	/**
	 * @return array
	 * @throws \Throwable
	 */
	public function getSystems(){
		return AuditTrail::getSystems();
	}
}