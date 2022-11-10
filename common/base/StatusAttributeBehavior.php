<?php

namespace common\base;

use Yii;
use yii\behaviors\AttributeBehavior;
use yii\db\BaseActiveRecord;

/**
 * Class StatusAttributeBehavior
 *
 * @package common\base
 */
class StatusAttributeBehavior extends AttributeBehavior{

	const STATUS_DELETED = - 10;

	const STATUS_ALL = - 1;

	const STATUS_INACTIVE = 0;

	const STATUS_ACTIVE = 10;

	public $status_attribute = 'status';

	public $value;

	/**
	 * @deprecated moved to use states() instead
	 */
	const STATES = [
		''                    => 'All',
		self::STATUS_ACTIVE   => 'Active',
		self::STATUS_INACTIVE => 'Inactive'
	];

	/**
	 * @inheritdoc
	 */
	public function init(){
		parent::init();

		if (empty($this->attributes)){
			$this->attributes = [
				BaseActiveRecord::EVENT_BEFORE_INSERT => [$this->status_attribute]
			];
		}
	}

	/**
	 * @inheritdoc
	 *
	 * In case, when the [[value]] property is `null`, the value of
	 *     `STATUS_ACTIVE` will be used as the value.
	 */
	protected function getValue($event){
		if ($this->value === NULL){
			return self::STATUS_ACTIVE;
		}

		return parent::getValue($event);
	}

	/**
	 * @return array
	 */
	public static function states(){
		return [
			self::STATUS_ALL      => Yii::t('common', 'All'),
			self::STATUS_ACTIVE   => Yii::t('common', 'Active'),
			self::STATUS_INACTIVE => Yii::t('common', 'Inactive')
		];
	}
}
