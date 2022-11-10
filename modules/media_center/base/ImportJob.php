<?php

namespace modules\media_center\base;

use Exception;
use modules\media_center\models\ImportLog;
use Yii;
use yii\base\BaseObject;
use yii\queue\RetryableJobInterface;
use yii\web\NotFoundHttpException;

/**
 * Class ImportJob.
 */
class ImportJob extends BaseObject implements RetryableJobInterface{

	public $import_id;

	/**
	 * @param \common\base\Queue $queue
	 *
	 * @return array
	 * @throws \yii\web\NotFoundHttpException|\yii\base\Exception
	 */
	public function execute($queue){
		if ($job = $this->findJob()){
			try{
				/**@var \modules\media_center\base\BaseImporter $importer */
				$importer        = new $job->import_class ();
				$importer->model = $job;

				return $importer->execute();
			}catch (Exception $exception){
				$job->status = ImportLog::STATUS_RETRY;
				$job->save(FALSE);

				throw new NotFoundHttpException($exception->getMessage());
			}
		}

		throw new NotFoundHttpException('Importer not found.');
	}

	/**
	 * @return \modules\media_center\models\ImportLog|null
	 */
	private function findJob(){
		$job = ImportLog::find()
		                ->andWhere(['status' => [ImportLog::STATUS_PENDING, ImportLog::STATUS_RETRY]])
		                ->andWhere(['id' => $this->import_id])
		                ->one();

		if (!empty($job) && class_exists($job->import_class) && file_exists($job->filename)){
			return $job;
		}

		return NULL;
	}

	/**
	 * @return int time to reserve in seconds
	 */
	public function getTtr(){
		return 5 * 60;
	}

	/**
	 * @param int $attempt number
	 * @param \Exception|\Throwable $error from last execute of the job
	 *
	 * @return bool
	 */
	public function canRetry($attempt, $error){
		Yii::error($error, self::class);

		if ($attempt < 5){
			return TRUE;
		}

		if ($job = $this->findJob()){
			$job->status = ImportLog::STATUS_ERROR;
			$job->save(FALSE);
		}

		return FALSE;
	}
}
