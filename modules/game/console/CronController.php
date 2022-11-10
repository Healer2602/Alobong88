<?php

namespace modules\game\console;

use modules\game\models\Betlog;
use modules\game\models\GamePlay;
use modules\matrix\base\Game;
use modules\matrix\models\Setting;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\ArrayHelper;

/**
 * Class CronController
 */
class CronController extends Controller{

	/**
	 * @return int
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\httpclient\Exception
	 */
	public function actionBetlog(){
		$plays = GamePlay::find()
		                 ->distinct()
		                 ->select(['product_code', 'player.currency'])
		                 ->joinWith('game game', FALSE)
		                 ->joinWith('player player', FALSE)
		                 ->andWhere(['>=', 'last_play', time() - 2 * 86400])
		                 ->orderBy(['last_play' => SORT_DESC])
		                 ->asArray()
		                 ->all();

		$total = 0;

		if (!empty($plays)){
			$setting = new Setting();
			$setting->getValues();
			$interval = $setting->interval_betlog;
			if (empty($interval)){
				$interval = 15;
			}

			$start_date = strtotime("-{$interval}minutes");

			$plays = ArrayHelper::index($plays, NULL, 'currency');
			foreach ($plays as $currency => $products){
				foreach ($products as $product_code){
					$data = Game::getBetlog($start_date, $product_code['product_code'], [
						'currency_code' => $currency
					]);

					foreach ($data as $datum){
						if (Betlog::store($datum)){
							$total ++;
						}
					}
				}
			}
		}

		$message = Yii::t('wallet',
			"Total {0} betlogs updated.", [$total]);

		echo $message . "\n";

		return ExitCode::OK;
	}
}
