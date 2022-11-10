<?php

namespace common\models;

use common\base\ActiveQuery;
use common\base\AuditTrailBehavior;
use common\base\Status;
use common\base\StatusAttributeBehavior;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\caching\DbDependency;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the base model class.
 *
 * @property bool $isRelated
 * @property array $statuses
 * @property string $languageLabel
 */
class BaseActiveRecord extends ActiveRecord{

	public static $alias = 'main_table';

	/**
	 * @return array
	 */
	public function behaviors(){
		return [
			'timestamp' => TimestampBehavior::class,
			'blameable' => [
				'class'        => BlameableBehavior::class,
				'defaultValue' => 0
			],
			'status'    => StatusAttributeBehavior::class,
			'audit'     => [
				'class'  => AuditTrailBehavior::class,
				'module' => Yii::t('common', 'System')
			]
		];
	}

	/**
	 * {@inheritdoc}
	 * @return ActiveQuery
	 */
	public static function find(){
		return new ActiveQuery(get_called_class());
	}

	/**
	 * @return bool
	 */
	public function getIsRelated()
	: bool{
		$related = $this->getRelatedRecords();
		$related = array_filter($related);

		if (empty($related)){
			return FALSE;
		}

		foreach ($related as $item){
			if (is_array($item)){
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * @param null|integer $status
	 * @param array $conditions
	 * @param string $from
	 * @param string $to
	 *
	 * @return array
	 * @throws \Throwable
	 */
	public static function findList(
		$status = NULL,
		$conditions = [],
		$from = 'id',
		$to = 'name'){
		$data = static::getDb()->cache(function ($data) use ($status, $conditions){
			$query = static::find();
			$query->andFilterWhere(['status' => $status]);

			if (!empty($conditions)){
				$query->andWhere($conditions);
			}

			return $query->asArray()->all();
		}, 0, new DbDependency([
			'sql' => "SELECT COUNT(*) + MAX(updated_at) from " . static::tableName()
		]));


		return ArrayHelper::map($data, $from, $to);
	}

	/**
	 * @return array
	 */
	public function getStatuses(){
		return [
			Status::STATUS_ACTIVE   => Yii::t('common', 'Active'),
			Status::STATUS_INACTIVE => Yii::t('common', 'Inactive')
		];
	}

	/**
	 * @return mixed|null
	 * @throws \Throwable
	 */
	public function getLanguageLabel(){
		if (!empty($this->language)){
			return Language::listLanguage()[$this->language] ?? NULL;
		}
	}
}
