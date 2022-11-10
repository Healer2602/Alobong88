<?php

namespace modules\wallet\models;

/**
 * Class TopupGateway
 *
 * @package modules\wallet\models
 */
class TopupGateway extends Gateway{

	/**
	 * @return \common\base\ActiveQuery
	 */
	public static function find(){
		return parent::find()->andWhere([self::$alias . '.type' => self::TYPE_TOPUP]);
	}

	/**
	 * @param bool $insert
	 *
	 * @return bool
	 */
	public function beforeSave($insert){
		if ($insert){
			$this->type = self::TYPE_TOPUP;
		}

		return parent::beforeSave($insert);
	}

	/**
	 * @param bool $insert
	 * @param array $changedAttributes
	 */
	public function afterSave($insert, $changedAttributes){
		if ($insert){
			static::updateAllCounters(['ordering' => 1], "id <> :id and type = :type",
				[':id' => $this->id, ':type' => static::TYPE_TOPUP]);
		}

		parent::afterSave($insert, $changedAttributes);
	}
}