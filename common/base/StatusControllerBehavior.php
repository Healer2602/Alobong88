<?php

namespace common\base;

use yii\base\Behavior;


/**
 * Change model status
 */
class StatusControllerBehavior extends Behavior{

	/**
	 * @var
	 */
	public $model;

	/**
	 * @param $id
	 * @param $status
	 *
	 * @return void
	 */
	public function changeStatus($id, $status){
		/** @var \yii\db\ActiveRecord $modelClass */
		$status       = ($status == StatusAttributeBehavior::STATUS_ACTIVE) ? StatusAttributeBehavior::STATUS_INACTIVE : StatusAttributeBehavior::STATUS_ACTIVE;
		$modelClass   = $this->model;
		$item         = $modelClass::findOne($id);
		$item->status = $status;

		return $item->save(FALSE);
	}

	/**
	 * @param $id
	 *
	 * @return bool
	 */
	public function softDelete($id){
		/** @var \yii\db\ActiveRecord $modelClass */
		$modelClass   = $this->model;
		$item         = $modelClass::findOne($id);
		$item->status = StatusAttributeBehavior::STATUS_DELETED;

		return $item->save(FALSE);
	}
}