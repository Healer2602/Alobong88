<?php

namespace modules\game\importers;

use common\models\AuditTrail;
use modules\game\models\Game;
use modules\game\models\GameDetail;
use modules\game\models\GameType;
use modules\game\models\Vendor;
use modules\media_center\base\BaseImporter;
use Yii;
use yii\helpers\Json;

/**
 * Game Importers
 */
class GameImporter extends BaseImporter{

	/**
	 * @return void
	 * @throws \PhpOffice\PhpSpreadsheet\Exception
	 * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
	 * @throws \yii\base\Exception
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function execute(){
		if ($data = parent::execute()){
			$errors = [];
			$total  = 0;
			$skip   = 0;
			$error  = 0;

			foreach ($data as $i => $row){
				if ($i == 1){
					continue;
				}

				$name         = (string) $row['B'];
				$name_zh      = (string) $row['C'];
				$name_vi      = (string) $row['D'];
				$vendor_name  = trim($row['E']) ?? NULL;
				$type_name    = trim($row['F']) ?? NULL;
				$code         = trim($row['G']);
				$lines        = (string) $row['H'] ?? NULL;
				$min_bet      = floatval($row['I']) ?? NULL;
				$max_bet      = floatval($row['J']) ?? NULL;
				$rtp          = (string) $row['K'] ?? NULL;
				$free_to_play = trim($row['L']) ?? NULL;
				$details      = [];

				if (empty($code)){
					$errors[$i] = Yii::t('game', 'Empty Code');
					$skip ++;
					break;
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

				if ($vendor_name && ($vendor_id = $this->getVendorId($vendor_name))){
					if ($type_name && ($type_id = $this->getTypeId($type_name))){
						$game = new Game([
							'name'         => $name,
							'vendor_id'    => $vendor_id,
							'type_id'      => $type_id,
							'code'         => $code,
							'lines'        => $lines,
							'min_bet'      => $min_bet,
							'max_bet'      => $max_bet,
							'rtp'          => $rtp,
							'free_to_play' => $free_to_play,
						]);

						if ($game->save()){
							$detail_data = [];
							foreach ($details as $language => $name){
								$detail_data[] = new GameDetail([
									'game_id'  => $game->id,
									'language' => $language,
									'name'     => $name
								]);
							}

							GameDetail::upsert($detail_data);
							$total ++;
						}else{
							$errors[$i] = Json::encode($game->errors);
							$error ++;
						}
					}else{
						$errors[$i] = Yii::t('game', 'Empty Game Type');
						$skip ++;
					}
				}else{
					$errors[$i] = Yii::t('game', 'Empty Vendor');
					$skip ++;
				}
			}

			AuditTrail::log('Import',
				Yii::t('game', 'Total {0} game(s) imported successfully', [$total]));

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
	 * @param $name
	 *
	 * @return int
	 */
	protected function getVendorId($name){
		$vendor = array_search($name, $this->findVendors());

		if (empty($vendor)){
			$model = new Vendor(['name' => $name]);
			if ($model->save()){
				$this->_vendors[$model->id] = $name;

				return $model->id;
			}
		}

		return $vendor;
	}

	/**
	 * @param $name
	 *
	 * @return int
	 */
	protected function getTypeId($name){
		$type = array_search($name, $this->findTypes());

		if (empty($type)){
			$model = new GameType(['name' => $name]);
			if ($model->save()){
				$this->_types[$model->id] = $name;

				return $model->id;
			}
		}

		return $type;
	}

	private $_types = NULL;

	/**
	 * @return array
	 */
	private function findTypes(){
		if ($this->_types === NULL){
			$this->_types = GameType::find()
			                        ->select(['name', 'id'])
			                        ->indexBy('id')
			                        ->limit(- 1)
			                        ->column();
		}

		return $this->_types;
	}

	private $_vendors = NULL;

	/**
	 * @return array
	 */
	private function findVendors(){
		if ($this->_vendors === NULL){
			$this->_vendors = Vendor::find()
			                        ->select(['name', 'id'])
			                        ->indexBy('id')
			                        ->limit(- 1)
			                        ->column();
		}

		return $this->_vendors;
	}
}