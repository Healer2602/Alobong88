<?php

namespace modules\notification\models;

use common\models\SettingForm;
use modules\notification\TelegramJob;
use Yii;

/**
 * Class TelegramSetting
 *
 * @package modules\notification\models
 */
class TelegramSetting extends SettingForm{

	public $telegram_url;
	public $telegram_withdraw_mention;

	/**
	 * @return array
	 */
	public function rules(){
		return [
			['telegram_url', 'required'],
			['telegram_url', 'url'],
			['telegram_withdraw_mention', 'string']
		];
	}

	/**
	 * @return array
	 */
	public function attributeLabels(){
		return [
			'telegram_url'              => Yii::t('common', 'Endpoint'),
			'telegram_withdraw_mention' => Yii::t('common', 'Withdrawal Mentions'),
		];
	}

	/**
	 * @param $message
	 *
	 * @return bool
	 */
	public static function addQueue($message){
		/**@var \yii\queue\db\Queue $queue */
		$queue = Yii::$app->queue;

		$queue->push(new TelegramJob([
			'message' => $message,
		]));

		return TRUE;
	}
}