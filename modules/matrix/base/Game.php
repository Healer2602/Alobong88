<?php

namespace modules\matrix\base;

use common\base\AppHelper;
use Mobile_Detect;
use modules\customer\models\Customer;
use modules\game\models\Game as GameModel;
use modules\promotion\models\Promotion;
use modules\promotion\models\PromotionJoining;
use Yii;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;

/**
 * Play Game
 */
class Game extends BaseObject{

	/**
	 * @param \modules\game\models\Game $model
	 * @param string $lobby_url
	 * @param bool $try
	 *
	 * @return false|int
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\httpclient\Exception
	 */
	public static function play(GameModel $model, $lobby_url, $try = FALSE){
		if (empty($model->productWallet->code)){
			return NULL;
		}

		if ($try){
			return self::launchFree($model, $lobby_url);
		}

		return self::launch($model, $lobby_url);
	}

	/**
	 * @param \modules\game\models\Game $model
	 * @param string $lobby_url
	 * @param bool $language
	 *
	 * @return mixed|null
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\httpclient\Exception
	 */
	private static function launch(GameModel $model, $lobby_url, $language = FALSE){
		/**@var Customer $player */
		$player = Yii::$app->user->identity;

		$body = [
			"PlayerId"      => $player->username,
			"GameCode"      => $model->code,
			"Language"      => $language ?: strtoupper(Yii::$app->language),
			"IpAddress"     => AppHelper::userIP(),
			"ProductWallet" => $model->productWallet->code,
			"IsDownload"    => 0
		];

		if (!empty($lobby_url)){
			$body["LobbyURL"] = $lobby_url;
		}

		$mobile = new Mobile_Detect();
		if ($mobile->isMobile()){
			$response = API::post('/Game/NewLaunchMobileGame', $body);
			if (!empty($response['GameUrl'])){
				return $response['GameUrl'];
			}

			if (empty($language) && $response === FALSE){
				return self::launch($model, $lobby_url, 'EN');
			}
		}

		$response = API::post('/Game/NewLaunchGame', $body);

		if (empty($language) && $response === FALSE){
			return self::launch($model, $lobby_url, 'EN');
		}

		return $response['GameUrl'] ?? NULL;
	}

	/**
	 * @param \modules\game\models\Game $model
	 * @param string $lobby_url
	 * @param bool $language
	 *
	 * @return mixed|null
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\httpclient\Exception
	 */
	private static function launchFree(GameModel $model, $lobby_url, $language = FALSE){
		$body = [
			"GameCode"      => $model->code,
			"Language"      => $language ?: strtoupper(Yii::$app->language),
			"IpAddress"     => AppHelper::userIP(),
			"ProductWallet" => $model->productWallet->code
		];

		if (!empty($lobby_url)){
			$body["LobbyURL"] = $lobby_url;
		}

		$mobile = new Mobile_Detect();
		if ($mobile->isMobile()){
			$response = API::post('/Game/LaunchFreeMobileGame', $body);
			if (!empty($response['GameUrl'])){
				return $response['GameUrl'];
			}

			if (empty($language) && $response === FALSE){
				return self::launchFree($model, $lobby_url, 'EN');
			}
		}

		$response = API::post('/Game/LaunchFreeGame', $body);

		if (empty($language) && $response === FALSE){
			return self::launchFree($model, $lobby_url, 'EN');
		}

		return $response['GameUrl'] ?? NULL;
	}

	const BETLOG_TIME_RANGE = 8;

	const BETLOG_DELAY = 10;

	/**
	 * @param $start_date
	 * @param $product_code
	 * @param array $data
	 *
	 * @return array
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\httpclient\Exception
	 */
	public static function getBetlog($start_date, $product_code, array $data = []){
		$start_date -= self::BETLOG_DELAY * 60;

		switch ($product_code){
			case 2:
			case 101:
			case 102:
			case 201:
				$logs = self::getSlotLog($start_date, $product_code, $data);
				break;
			case 301:
			case 401:
			case 431:
				$logs = self::getSportLog($start_date, $product_code);
				break;
			case 501:
			case 502:
			case 503:
			case 504:
				$logs = self::getLotteryLog($start_date, $product_code, $data);
				break;
			case 602:
			case 603:
			case 604:
			case 606:
			case 607:
			case 609:
			case 610:
			case 611:
			case 612:
			case 613:
			case 614:
				$logs = self::getIMLog($start_date, $product_code, $data);
				break;
			case 801:
			case 902:
				$logs = self::getCasinoLog($start_date, $product_code, $data);
				break;
			default:
				$logs = [];
		}

		return $logs;
	}

	/**
	 * @param $start_date
	 * @param $product_code
	 * @param $data
	 *
	 * @return array
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\httpclient\Exception
	 */
	private static function getIMLog($start_date, $product_code, $data){
		$body = [
			"StartDate"     => Yii::$app->formatter->asDatetime($start_date, 'php:Y-m-d H.i.s'),
			"EndDate"       => Yii::$app->formatter->asDatetime($start_date + self::BETLOG_TIME_RANGE * 60,
				'php:Y-m-d H.i.s'),
			"ProductWallet" => $product_code,
			"Language"      => "EN",
			"Page"          => 1,
			"Currency"      => $data['currency_code'] ?? '',
			'PageSize'      => 2000
		];

		$data  = [];
		$items = [];
		$logs  = self::betlog($data, $body);
		if (!empty($logs)){
			foreach ($logs as $log){
				$bet_id = $log['BetId'] ?? NULL;
				if (!empty($log['RoundId']) || !empty($log['RoundID'])){
					$round_id = $log['RoundId'] ?? $log['RoundID'];
					$bet_id   = $round_id . '_' . $bet_id;
				}

				$items[] = [
					'bet_id'            => $bet_id,
					'created_at'        => strtotime($log['DateCreated'] ?? NULL),
					'updated_at'        => strtotime($log['LastUpdateddate'] ?? $log['LastUpdatedDate'] ?? NULL),
					'player_id'         => $log['PlayerId'] ?? $log['PlayerName'] ?? NULL,
					'provider'          => $log['Provider'] ?? NULL,
					'game_code'         => $log['GameId'] ?? NULL,
					'wallet_code'       => $product_code,
					'amount'            => $log['BetAmount'] ?? 0,
					'winloss'           => $log['WinLoss'] ?? 0,
					'turnover_wo_bonus' => abs($log['WinLoss'] ?? 0),
				];
			}
		}

		return $items;
	}

	/**
	 * @param $start_date
	 * @param $product_code
	 * @param $data
	 *
	 * @return array
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\httpclient\Exception
	 */
	private static function getCasinoLog($start_date, $product_code, $data){
		$body = [
			"StartDate"     => Yii::$app->formatter->asDatetime($start_date, 'php:Y-m-d H.i.s'),
			"EndDate"       => Yii::$app->formatter->asDatetime($start_date + self::BETLOG_TIME_RANGE * 60,
				'php:Y-m-d H.i.s'),
			"ProductWallet" => $product_code,
			"Language"      => "EN",
			"Page"          => 1,
			"Currency"      => $data['currency_code'] ?? '',
			'PageSize'      => 2000
		];

		$data  = [];
		$items = [];
		$logs  = self::betlog($data, $body);
		if (!empty($logs)){
			foreach ($logs as $log){
				$bet_id = $log['BetId'];
				if (!empty($log['RoundId'])){
					$bet_id = $log['RoundID'] . '_' . $bet_id;
				}

				$items[] = [
					'bet_id'            => $bet_id,
					'created_at'        => strtotime($log['DateCreated'] ?? NULL),
					'updated_at'        => strtotime($log['LastUpdatedDate'] ?? NULL),
					'player_id'         => $log['PlayerId'] ?? $log['PlayerName'] ?? NULL,
					'provider'          => $log['Provider'] ?? NULL,
					'game_code'         => $log['GameId'] ?? NULL,
					'wallet_code'       => $product_code,
					'amount'            => $log['BetAmount'] ?? 0,
					'winloss'           => $log['WinLoss'] ?? 0,
					'turnover_wo_bonus' => abs($log['WinLoss'] ?? 0),
				];
			}
		}

		return $items;
	}

	/**
	 * @param $start_date
	 * @param $product_code
	 * @param $data
	 *
	 * @return array
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\httpclient\Exception
	 */
	private static function getLotteryLog($start_date, $product_code, $data){
		$body = [
			"StartDate"     => Yii::$app->formatter->asDatetime($start_date, 'php:Y-m-d H.i.s'),
			"EndDate"       => Yii::$app->formatter->asDatetime($start_date + self::BETLOG_TIME_RANGE * 60,
				'php:Y-m-d H.i.s'),
			"ProductWallet" => $product_code,
			"Language"      => "EN",
			"Page"          => 1,
			"Currency"      => $data['currency_code'] ?? '',
			'PageSize'      => 2000
		];

		$data  = [];
		$items = [];
		$logs  = self::betlog($data, $body);
		if (!empty($logs)){
			foreach ($logs as $log){
				$bet_id = $log['BetId'] ?? NULL;
				if (!empty($log['RoundId'])){
					$bet_id = $log['RoundID'] . '_' . $bet_id;
				}

				$items[] = [
					'bet_id'            => $bet_id,
					'created_at'        => strtotime($log['BetDate'] ?? NULL),
					'updated_at'        => strtotime($log['LastUpdatedDate'] ?? NULL),
					'player_id'         => $log['PlayerId'] ?? $log['PlayerName'] ?? NULL,
					'provider'          => $log['Provider'] ?? NULL,
					'game_code'         => $log['GameId'] ?? NULL,
					'wallet_code'       => $product_code,
					'amount'            => $log['BetAmount'] ?? 0,
					'winloss'           => $log['WinLoss'] ?? 0,
					'turnover_wo_bonus' => abs($log['WinLoss'] ?? 0),
				];
			}
		}

		return $items;
	}

	/**
	 * @param $start_date
	 * @param $product_code
	 * @param $data
	 *
	 * @return array
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\httpclient\Exception
	 */
	private static function getSlotLog($start_date, $product_code, $data){
		$body = [
			"StartDate"     => Yii::$app->formatter->asDatetime($start_date, 'php:Y-m-d H.i.s'),
			"EndDate"       => Yii::$app->formatter->asDatetime($start_date + self::BETLOG_TIME_RANGE * 60,
				'php:Y-m-d H.i.s'),
			"ProductWallet" => $product_code,
			"Language"      => "EN",
			"Page"          => 1,
			"Currency"      => $data['currency_code'] ?? '',
			'PageSize'      => 2000
		];

		$data  = [];
		$items = [];
		$logs  = self::betlog($data, $body);
		if (!empty($logs)){
			foreach ($logs as $log){
				$items[] = [
					'bet_id'            => $log['BetId'] ?? $log['SessionId'] ?? $log['RoundId'] ?? $log['ProviderRoundId'] ?? NULL,
					'created_at'        => strtotime($log['BetDate'] ?? $log['GameDate'] ?? $log['DateCreated'] ?? NULL),
					'updated_at'        => strtotime($log['LastUpdatedDate'] ?? $log['GameDate'] ?? NULL),
					'player_id'         => $log['PlayerId'] ?? $log['PlayerName'] ?? NULL,
					'provider'          => $log['Provider'] ?? NULL,
					'game_code'         => $log['GameId'] ?? NULL,
					'wallet_code'       => $product_code,
					'amount'            => $log['Bet'] ?? $log['BetAmount'] ?? 0,
					'winloss'           => $log['Win'] ?? $log['WinLoss'] ?? $log['ValidBet'] ?? 0,
					'turnover_wo_bonus' => abs($log['WinLoss'] ?? 0),
				];
			}
		}

		return $items;
	}

	/**
	 * @param $start_date
	 * @param $product_code
	 *
	 * @return array
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\httpclient\Exception
	 */
	private static function getSportLog($start_date, $product_code){
		$body = [
			"StartDate"      => Yii::$app->formatter->asDatetime($start_date, 'php:Y-m-d H.i.s'),
			"EndDate"        => Yii::$app->formatter->asDatetime($start_date + self::BETLOG_TIME_RANGE * 60,
				'php:Y-m-d H.i.s'),
			"ProductWallet"  => $product_code,
			"Language"       => "EN",
			"Page"           => 1,
			"DateFilterType" => 1
		];

		$data  = [];
		$items = [];
		$logs  = self::betlog($data, $body);
		if (!empty($logs)){
			foreach ($logs as $log){
				$promo = self::findPromotion($log['PlayerId'] ?? $log['PlayerName'] ?? NULL,
					$product_code, 'sport',
					$log);

				$promo_data = [];
				if (!empty($promo)){
					$promo_data = $promo;
				}

				$items[] = [
					           'bet_id'      => $log['BetId'] ?? NULL,
					           'created_at'  => strtotime($log['WagerCreationDateTime'] ?? $log['GameDate'] ?? $log['DateCreated'] ?? NULL),
					           'updated_at'  => strtotime($log['LastUpdatedDate'] ?? NULL),
					           'player_id'   => $log['PlayerId'] ?? $log['PlayerName'] ?? NULL,
					           'provider'    => $log['Provider'] ?? NULL,
					           'game_code'   => $log['GameId'] ?? NULL,
					           'wallet_code' => $product_code,
					           'amount'      => abs($log['StakeAmount'] ?? $log['BetAmount'] ?? 0),
					           'winloss'     => $log['WinLoss'] ?? $log['Win'] ?? $log['ValidBet'] ?? 0
				           ] + $promo_data;
			}
		}

		return $items;
	}

	/**
	 * @param array $data
	 * @param array $body
	 *
	 * @return array
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\httpclient\Exception
	 */
	private static function betlog(&$data, $body){
		$response = API::post('/Report/GetBetLog', $body);

		if (!empty($response)){
			if (!empty($response['Result'])){
				$data = ArrayHelper::merge($data, $response['Result']);
			}

			if (!empty($response['Pagination']['CurrentPage']) && !empty($response['Pagination']['TotalPage']) && $response['Pagination']['CurrentPage'] < $response['Pagination']['TotalPage']){
				$body['Page'] = $response['Pagination']['CurrentPage'] + 1;

				return self::betlog($data, $body);
			}
		}

		return $data;
	}

	/**
	 * @param $player_id
	 * @param $code
	 * @param $prefix
	 * @param $log
	 *
	 * @return array|null
	 */
	private static function findPromotion($player_id, $code, $prefix, $log){
		$promotion = PromotionJoining::joiningPromotion($player_id, $code);
		if (!empty($promotion)){
			$class = "modules\\promotion\\conditions\\{$prefix}_{$promotion['promotion_type']}";
			if (class_exists($class)){
				/**@var \modules\promotion\conditions\sport_first_deposit $model */
				$model = new $class;

				return [
					'amount'               => $model->getAmount($log),
					'winloss'              => $model->getWinLoss($log),
					'promotion_id'         => $promotion['promotion_id'],
					'promotion_joining_id' => $promotion['id'],
					'rate'                 => $promotion['rate'],
					'bonus'                => $promotion['bonus'],
					'is_rebate'            => $promotion['promotion_type'] != Promotion::TYPE_FIRST_DEPOSIT
				];
			}
		}

		return [];
	}
}