<?php

namespace common\base;

use Yii;

/**
 * Class ActiveQuery
 *
 * @package common\models
 * {{@inheritdoc}}
 * @see \yii\db\ActiveRecord
 *
 */
class ActiveQuery extends \yii\db\ActiveQuery{

	/**
	 * @var string
	 */
	protected $_alias;

	/**
	 * @inheritDoc
	 */
	public function init(){
		/**@var \common\models\BaseActiveRecord $model */
		$model = $this->modelClass;

		$this->_alias = $model::$alias ?? 'main_table';
		$this->alias($this->_alias);

		parent::init();
	}

	/**
	 * @param int $state
	 *
	 * @return \common\base\ActiveQuery
	 */
	public function status($state = 10){
		return $this->andWhere([$this->_alias . '.status' => $state]);
	}

	/**
	 * @return \common\base\ActiveQuery
	 */
	public function notDeleted(){
		return $this->andWhere([
				"<>",
				$this->_alias . '.status',
				Status::STATUS_DELETED
			]
		);
	}

	/**
	 * @return \common\base\ActiveQuery
	 */
	public function default(){
		return $this->status(Status::STATUS_ACTIVE);
	}

	/**
	 * @return \common\base\ActiveQuery
	 */
	public function translate(){
		return $this->andWhere([$this->_alias . '.language' => Yii::$app->language]);
	}
}