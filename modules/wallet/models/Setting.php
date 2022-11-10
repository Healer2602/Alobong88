<?php


namespace modules\wallet\models;


use common\models\SettingForm;
use Yii;

/**
 * Class Setting
 *
 * @package modules\wallet\models
 */
class Setting extends SettingForm{

	public $minimum_topup;
	public $minimum_topup_first;
	public $maximum_topup;
	public $topup_auto_reject;
	public $minimum_withdraw;
	public $maximum_withdraw;
	public $maximum_withdraw_wo_kyc;
	public $withdraw_limit_balance;
	public $daily_limit_balance;
	public $daily_count_balance;

	/**
	 * @return array
	 */
	public function rules(){
		return [
			[['minimum_withdraw', 'minimum_topup', 'minimum_topup_first', 'maximum_withdraw', 'maximum_topup'], 'required'],
			[['minimum_withdraw', 'minimum_topup', 'minimum_topup_first', 'maximum_withdraw', 'maximum_topup'], 'number', 'min' => 0],
			['topup_auto_reject', 'double', 'min' => 1],
			[['daily_limit_balance', 'daily_count_balance', 'withdraw_limit_balance'], 'required'],
			[['daily_limit_balance', 'daily_count_balance', 'withdraw_limit_balance', 'maximum_withdraw_wo_kyc'], 'number'],
		];
	}

	/**
	 * @return array
	 */
	public function attributeLabels(){
		return [
			'minimum_topup'           => Yii::t('wallet', 'Minimum Deposit amount'),
			'minimum_topup_first'     => Yii::t('wallet', 'Minimum First Deposit amount'),
			'maximum_topup'           => Yii::t('wallet', 'Maximum Deposit amount for unverified'),
			'minimum_withdraw'        => Yii::t('wallet', 'Minimum Withdraw'),
			'maximum_withdraw'        => Yii::t('wallet', 'Maximum Withdraw without approval'),
			'topup_auto_reject'       => Yii::t('wallet', 'Time to cancel deposit'),
			'daily_limit_balance'     => Yii::t('wallet', 'Daily Limit Balance'),
			'daily_count_balance'     => Yii::t('wallet', 'Daily Count Balance'),
			'withdraw_limit_balance'  => Yii::t('wallet', 'Withdraw Limit Balance'),
			'maximum_withdraw_wo_kyc' => Yii::t('wallet', 'Maximum Withdraw without KYC'),
		];
	}
}