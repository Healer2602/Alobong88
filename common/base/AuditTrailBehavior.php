<?php

namespace common\base;

use common\models\AuditTrail;
use ReflectionClass;
use ReflectionException;
use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\helpers\Inflector;

/**
 * Class AuditTrailBehavior
 *
 * @package common\base
 */
class AuditTrailBehavior extends Behavior{

	public $field = 'name';
	public $module = '';
	public $category = '';
	public $action = '';
	public $name;

	/**
	 * @return array
	 */
	public function events(){
		return [
			ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
			ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
			ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete'
		];
	}

	/**
	 * @param \yii\base\Event $event
	 */
	public function afterUpdate($event){
		$attributes = $event->changedAttributes;
		unset($attributes['updated_at'], $attributes['updated_by']);

		if (!empty($attributes)){
			$category = $this->_class2Name($event->sender);
			$action   = 'update';

			if (isset($attributes['status']) && count($attributes) == 1){
				switch ($event->sender->status){
					case Status::STATUS_ACTIVE:
						$action = 'activate';
						break;
					case Status::STATUS_INACTIVE:
						$action = "deactivate";
						break;
					case Status::STATUS_DELETED:
						$action = 'delete';
						break;
				}
			}

			$message = Yii::t('common', '{0} {1}: {2}', [
				Yii::t('common', ucfirst($action)),
				mb_strtolower($this->name ?: $category, 'UTF-8'),
				$this->_getItemAttribute($event->sender)
			]);

			$audit_action = $this->findAction(Yii::t('common', 'Update'));
			if ($action == 'delete'){
				$audit_action = Yii::t('common', 'Delete');
			}

			AuditTrail::log($audit_action, $message, $this->module);
		}
	}

	/**
	 * @param \yii\base\Event $event
	 */
	public function afterInsert($event){
		$action   = $this->findAction(Yii::t('common', 'Create'));
		$category = $this->_class2Name($event->sender);
		$message  = Yii::t('common', 'Create new {0}: {1}',
			[mb_strtolower($this->name ?: $category,
				'UTF-8'), $this->_getItemAttribute($event->sender)]);

		AuditTrail::log($action, $message, $this->module);
	}

	/**
	 * @param \yii\base\Event $event
	 */
	public function afterDelete($event){
		$action   = Yii::t('common', 'Delete');
		$category = $this->_class2Name($event->sender);
		$message  = Yii::t('common', 'Delete {0}: {1}',
			[mb_strtolower($this->name ?: $category,
				'UTF-8'), $this->_getItemAttribute($event->sender)]);

		AuditTrail::log($action, $message, $this->module);
	}

	/**
	 * @param $action
	 *
	 * @return mixed|string
	 */
	private function findAction($action = ''){
		return $this->action ?: $action ?? '';
	}

	/**
	 * @param ActiveRecord $class_name
	 *
	 * @return string
	 */
	private function _class2Name($class_name){
		try{
			if (!empty($this->category)){
				return $this->category;
			}

			$class = (new ReflectionClass($class_name))->getShortName();

			if (!empty($class)){
				$class = str_replace(["Form"], "", $class);
				$class = Inflector::titleize($class, TRUE);
			}

			return Yii::t('common', trim($class));

		}catch (ReflectionException $exception){
			return '';
		}
	}

	/**
	 * @param ActiveRecord $sender
	 *
	 * @return mixed|string
	 */
	private function _getItemAttribute($sender){
		$attribute = '';
		if ($this->field){
			$attribute = $sender->{$this->field} ?? '';
		}

		if (empty($attribute)){
			$primary_keys = $sender->getPrimaryKey(TRUE);
			$attribute    = reset($primary_keys) . "(" . key($primary_keys) . ")";
		}

		return $attribute;
	}

}