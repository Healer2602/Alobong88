<?php

namespace modules\wallet\models;

/**
 * Class WithdrawGateway
 *
 * @package modules\wallet\models
 */
class WithdrawGateway extends Gateway{

	/**
	 * @return \common\base\ActiveQuery
	 */
	public static function find(){
		return parent::find()->andWhere([self::$alias . '.type' => self::TYPE_WITHDRAW]);
	}

	/**
	 * @param bool $insert
	 *
	 * @return bool
	 */
	public function beforeSave($insert){
		if ($insert){
			$this->type = self::TYPE_WITHDRAW;
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
				[':id' => $this->id, ':type' => static::TYPE_WITHDRAW]);
		}

		parent::afterSave($insert, $changedAttributes);
	}
}