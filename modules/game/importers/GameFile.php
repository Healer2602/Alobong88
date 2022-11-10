<?php

namespace modules\game\importers;

use common\base\AppHelper;
use common\base\Status;
use common\models\AuditTrail;
use Google\Service\AdMob\App;
use modules\game\models\Game;
use modules\game\models\GameDetail;
use modules\game\models\GameType;
use modules\game\models\Vendor;
use modules\media_center\base\BaseImporter;
use modules\media_center\models\ImportLog;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;
use ZipArchive;

/**
 * Game File Update
 */
class GameFile extends BaseImporter{

	/**
	 * @return void
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function execute(){
		$model = $this->model;

		if (empty($model->filename) || !file_exists($model->filename)){
			throw new NotFoundHttpException('Import data file not found.');
		}

		if (empty($model) || $model->status != ImportLog::STATUS_PENDING){
			return NULL;
		}

		$model->status = ImportLog::STATUS_IN_PROGRESS;
		if ($model->save(FALSE)){
			$errors = [];
			$total  = 0;
			$skip   = 0;
			$error  = 0;
			$field  = "game/" . $model->id;
			$path   = AppHelper::uploadPath($field);
			$zip    = new ZipArchive;

			if ($zip->open($model->filename) === TRUE){
				$zip->extractTo($path);
				$zip->close();

				if ($zip->status == 0){
					$files = FileHelper::findFiles($path,
						[
							'only'   => ['*.jpg', '*.jpeg', '*.png', '*.gif'],
							'filter' => function ($path){
								return strpos($path, 'en') !== FALSE;
							}
						]);

					if (!empty($files)){
						for ($i = 0; $i < count($files); $i ++){
							$img_path = $files[$i];
							$img_info = pathinfo($img_path);
							$name     = trim($img_info['filename']);
							$ext      = strtolower($img_info['extension']);
							$basename = $img_info['basename'];

							if (ArrayHelper::isIn($ext, $this->typeImages())){
								if ($game = $this->getGameByName($name)){
									$game->icon = AppHelper::uploadUri("{$field}/en", $basename);

									if ($game->save(FALSE)){
										$total ++;

										if ($this->storeDetail($game, Game::LANGUAGE_VI, $field)){
											$total ++;
										}else{
											$skip ++;
										}

										if ($this->storeDetail($game, Game::LANGUAGE_ZH, $field)){
											$total ++;
										}else{
											$skip ++;
										}

										continue;
									}

								}else{
									$errors[$i] = Yii::t('game', 'Empty Game');
								}
							}else{
								$errors[$i] = Yii::t('game',
									'The file is not in the correct format');
							}
							$skip ++;
						}
					}else{
						$errors[] = Yii::t('game', 'Empty Files');
						$skip ++;
					}
				}
			}else{
				$errors[] = Yii::t('game', 'Extract file failed');
				$skip ++;
			}

			AuditTrail::log('Import',
				Yii::t('game', 'Total {0} files(s) imported successfully', [$total]));

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
	 * @return \modules\game\models\Game|null
	 */
	private function getGameByName($name){
		if (empty($name)){
			return NULL;
		}

		return Game::find()->andFilterWhere(['LOWER(name)' => strtolower($name)])->one();
	}

	/**
	 * @param $field
	 * @param $name
	 *
	 * @return string|null
	 */
	private function scanFile($field, $name)
	: ?string{
		foreach ($this->typeImages() as $type){
			$filename = "{$name}.{$type}";
			$path     = AppHelper::uploadPath($field, $filename);

			if (file_exists($path)){
				return AppHelper::uploadUri($field, $filename);
			}
		}

		return NULL;
	}

	/**
	 * @return string[]
	 */
	private function typeImages()
	: array{
		return ['jpg', 'jpeg', 'png', 'gif'];
	}

	/**
	 * @param \modules\game\models\Game $game
	 * @param $language
	 * @param $field
	 *
	 * @return null
	 */
	private function storeDetail(Game $game, $language, $field){
		$field_file = "{$field}/{$language}";
		if ($icon = $this->scanFile($field_file, $game->name)){
			$detail = [];
			if ($language == Game::LANGUAGE_VI){
				$detail = $game->detailVi;
			}
			if ($language == Game::LANGUAGE_ZH){
				$detail = $game->detailZh;
			}

			if (!empty($detail)){
				$detail->icon = $icon;

				return $detail->save(FALSE);
			}
		}

		return NULL;
	}
}