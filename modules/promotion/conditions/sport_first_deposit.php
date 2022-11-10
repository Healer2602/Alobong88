<?php

namespace modules\promotion\conditions;

use yii\base\BaseObject;
use yii\helpers\ArrayHelper;

/**
 * Sport First Deposit Logic
 */
class sport_first_deposit extends BaseObject{ //NOSONAR

	const ODD_HK_MALAY = .5;

	const ODD_INDO = - 2;

	const ODD_EURO = 1.5;

	/**
	 * @param array $log from Betlog
	 *
	 * @return float
	 */
	public function getWinLoss($log){
		if (!$this->isAllow($log)){
			return 0;
		}

		return $log['WinLoss'] ?? 0;
	}

	/**
	 * @param array $log from Betlog
	 *
	 * @return float
	 */
	public function getAmount($log){
		if (!$this->isAllow($log)){
			return 0;
		}

		return $log['StakeAmount'] ?? 0;
	}

	/**
	 * @param array $log from Betlog
	 *
	 * @return bool
	 */
	private function isAllow($log)
	: bool{
		if (empty($log) || empty($log['OddsType'])){
			return 0;
		}

		if (!ArrayHelper::isIn($log['OddsType'], ['HK', 'MALAY', 'INDO', 'EURO'])){
			return 0;
		}

		$odds = $this->getMinOdds($log);

		if (empty($odds)){
			return FALSE;
		}

		switch ($log['OddsType']){
			case 'INDO':
				$min = self::ODD_INDO;
				break;
			case 'EURO':
				$min = self::ODD_EURO;
				break;
			default:
				$min = self::ODD_HK_MALAY;
		}

		return $odds >= $min;
	}

	/**
	 * @param array $log from Betlog
	 *
	 * @return int|mixed
	 */
	private function getMinOdds($log){
		$details = $log['DetailItems'] ?? [];
		if (empty($details)){
			return 0;
		}

		$odds = ArrayHelper::getColumn($details, function ($data) use ($log){
			$odds = $data['Odds'] ?? 0;
			if ($log['OddsType'] == 'MALAY' || $log['OddsType'] == 'EURO'){
				return abs($odds);
			}

			return $odds;
		});

		return min($odds);
	}
}