<?php

namespace modules\game\importers;

use common\base\Status;
use common\models\AuditTrail;
use modules\game\models\Game;
use modules\game\models\GameDetail;
use modules\media_center\base\BaseImporter;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Game Update
 */
class GameUpdate extends GameImporter{

	const STATUS_NEW = 'New';

	const STATUS_DISABLE = 'Disable';

	const STATUS_ENABLE = 'Enable';

	const FEATURED = 'Featured';

	/**
	 * @return void
	 * @throws \PhpOffice\PhpSpreadsheet\Exception
	 * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
	 * @throws \yii\base\Exception
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function execute(){
		if ($data = BaseImporter::execute()){
			$errors = [];
			$total  = 0;
			$skip   = 0;
			$error  = 0;

			foreach ($data as $i => $row){
				if ($i == 1){
					continue;
				}

				$name         = $row['B'];
				$name_zh      = $row['C'];
				$name_vi      = $row['D'];
				$vendor_name  = trim($row['E']) ?? NULL;
				$type_name    = trim($row['F']) ?? NULL;
				$code         = trim($row['G']) ?? NULL;
				$lines        = $row['H'];
				$min_bet      = $row['I'];
				$max_bet      = $row['J'];
				$rtp          = (string) $row['K'];
				$status       = trim($row['L']) ?? NULL;
				$free_to_play = trim($row['M']) ?? NULL;
				$details      = [];

				if (empty($code)){
					$errors[$i] = Yii::t('game', 'Empty Code');
					$skip ++;
					continue;
				}

				if (empty($status)){
					$errors[$i] = Yii::t('game', 'Empty Status');
					$skip ++;
					continue;
				}

				if (!empty($name_vi)){
					$details[Game::LANGUAGE_VI] = $name_vi;
				}

				if (!empty($name_zh)){
					$details[Game::LANGUAGE_ZH] = $name_zh;
				}

				if (!empty($free_to_play) && !strcmp(strtoupper($free_to_play),
						Game::FREE_TO_PLAY_KEY)){
					$free_to_play = Game::FREE_TO_PLAY_VALUE;
				}else{
					$free_to_play = 0;
				}

				$game = NULL;
				if ($status == self::STATUS_DISABLE){
					if ($game = $this->getGameByCode($code)){
						$game->status = Status::STATUS_INACTIVE;
					}else{
						$errors[$i] = Yii::t('game', 'Game not found');
						$skip ++;
						continue;
					}
				}

				if ($this->isNewOrUpdate($status)){
					if ($vendor_name && ($vendor_id = $this->getVendorId($vendor_name))){
						if ($type_name && ($type_id = $this->getTypeId($type_name))){
							$new_game = new Game([
								'name'         => $name,
								'vendor_id'    => $vendor_id,
								'type_id'      => $type_id,
								'code'         => $code,
								'lines'        => $lines,
								'min_bet'      => $min_bet,
								'max_bet'      => $max_bet,
								'rtp'          => $rtp,
								'status'       => Status::STATUS_ACTIVE,
								'free_to_play' => $free_to_play
							]);
						}else{
							$errors[$i] = Yii::t('game', 'Empty Game Type');
							$skip ++;
							continue;
						}
					}else{
						$errors[$i] = Yii::t('game', 'Empty Vendor');
						$skip ++;
						continue;
					}

					/** Action by Status */
					if ($status == self::FEATURED || $status == self::STATUS_ENABLE){
						$game = $this->getGameByCode($code);
						if (empty($game)){
							$game = $new_game;
						}elseif ($status == self::STATUS_ENABLE){
							$game->status = Status::STATUS_ACTIVE;
						}

						if ($status == self::FEATURED){
							$game->feature = Game::FEATURED;
						}
					}

					if ($status == self::STATUS_NEW){
						$game = $new_game;
					}
				}

				/** Save Data */
				if (!empty($game)){
					$is_new             = $game->isNewRecord;
					$game->free_to_play = $free_to_play;

					if ($game->save()){
						/** Create details if it's new record. */
						if ($is_new){
							$detail_data = [];
							foreach ($details as $language => $name){
								$detail_data[] = new GameDetail([
									'game_id'  => $game->id,
									'language' => $language,
									'name'     => $name
								]);
							}

							GameDetail::upsert($detail_data);
						}
						$total ++;
					}else{
						$errors[$i] = Json::encode($game->errors);
						$error ++;
					}
				}else{
					$errors[$i] = Yii::t('game', 'Empty Data');
					$skip ++;
				}
			}

			AuditTrail::log('Import',
				Yii::t('game', 'Total {0} game(s) updated successfully', [$total]));

			$report = [
				'errors' => $errors,
				'total'  => $total,
				'skip'   => $skip,
				'error'  => $error
			];

			$this->markAsCompleted($report);
		}
	}

	/**
	 * @param $code
	 *
	 * @return \modules\game\models\Game|null
	 */
	private function getGameByCode($code){
		if (empty($code)){
			return NULL;
		}

		return Game::findOne(['code' => $code]);
	}

	/**
	 * @param $status
	 *
	 * @return bool
	 */
	private function isNewOrUpdate($status){
		return ArrayHelper::isIn($status,
			[self::STATUS_NEW, self::STATUS_ENABLE, self::FEATURED]);
	}
}