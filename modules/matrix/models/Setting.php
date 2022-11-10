<?php

namespace modules\matrix\models;

use common\models\SettingForm;
use Yii;

/**
 * Class Setting
 *
 * @package modules\matrix\models
 */
class Setting extends SettingForm{

	public $endpoint;
	public $merchant_parent;
	public $merchant_name;
	public $merchant_code;
	public $merchant_prefix;

	public $interval_betlog;
	public $interval_wallet;

	/**
	 * @return array
	 */
	public function rules(){
		return [
			[['endpoint', 'merchant_parent', 'merchant_name', 'merchant_code', 'merchant_prefix'], 'required'],
			['endpoint', 'url'],
			[['merchant_parent', 'merchant_name', 'merchant_code', 'merchant_prefix'], 'string'],

			[['interval_betlog', 'interval_wallet'], 'required'],
			[['interval_betlog', 'interval_wallet'], 'number', 'min' => 1],
		];
	}

	/**
	 * @return array
	 */
	public function attributeLabels(){
		return [
			'endpoint'        => Yii::t('matrix', 'API Endpoint URL'),
			'merchant_parent' => Yii::t('matrix', 'Merchant Parent Name'),
			'merchant_name'   => Yii::t('matrix', 'Merchant Name'),
			'merchant_code'   => Yii::t('matrix', 'Merchant Code'),
			'merchant_prefix' => Yii::t('matrix', 'Prefix'),
			'interval_betlog' => Yii::t('matrix', 'Betlog Interval (minutes)'),
			'interval_wallet' => Yii::t('matrix', 'Wallet Balance Sync Interval (minutes)'),
		];
	}

	/**
	 * @return array
	 */
	public function attributeHints(){
		return [
			'interval_betlog' => Yii::t('matrix', 'Time between every run'),
			'interval_wallet' => Yii::t('matrix', 'Last active of the player'),
		];
	}
}