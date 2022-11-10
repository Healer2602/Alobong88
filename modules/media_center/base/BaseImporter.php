<?php

namespace modules\media_center\base;

use common\base\AppHelper;
use common\models\AuditTrail;
use modules\media_center\models\ImportLog;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Yii;
use yii\db\Exception;
use yii\web\NotFoundHttpException;

/**
 * Class BaseImporter
 *
 * @package modules\media_center\base
 */
abstract class BaseImporter implements BaseImporterInterface{

	/**
	 * @var ImportLog
	 */
	public $model;

	/**
	 * @return bool|array
	 * @throws \PhpOffice\PhpSpreadsheet\Exception
	 * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
	 * @throws \yii\base\Exception
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
			return $this->extractData();
		}

		return FALSE;
	}

	/**
	 * @param array $report
	 *
	 * @return bool
	 */
	public function markAsCompleted($report = []){
		$this->model->status = ImportLog::STATUS_DONE;
		$description         = [];

		if (!isset($report['total'])){
			$description['total_imports'] = array_sum($report);
		}else{
			$description['total_imports'] = $report['total'] ?: 0;
		}

		if (!empty($report['errors'])){
			$this->model->error_log = $this->_generateErrorLog($report['errors'],
				$this->model->filename);
		}

		$description['total_errors'] = $report['error'] ?? 0;
		$description['total_skips']  = $report['skip'] ?? 0;

		try{
			$this->model->description = $description;

			return $this->model->save(FALSE);
		}catch (\Exception $exception){
			$this->log($exception->getMessage(), AuditTrail::STATUS_FAILED);
		}
	}

	/**
	 * @return array
	 * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
	 *
	 * @throws \PhpOffice\PhpSpreadsheet\Exception
	 */
	public function extractData(){
		$reader      = IOFactory::createReader('Xlsx');
		$spreadsheet = $reader->load($this->model->filename);

		return $spreadsheet->getActiveSheet()->toArray(NULL, FALSE, FALSE, TRUE);
	}

	/**
	 * @param $columns
	 *
	 * @return int
	 */
	public function createTemporaryTable($columns){
		try{
			Yii::$app->db->createCommand()
			             ->createTable($this->_getTableName(), $columns)
			             ->execute();

			return $this->_getTableName();

		}catch (Exception $e){
			return FALSE;
		}
	}

	/**
	 * @return string
	 */
	private function _getTableName(){
		$table_name = 'importer_' . strtolower($this->model->importer);

		if (Yii::$app->db->driverName == 'mysql'){
			return $table_name;
		}

		return "#$table_name";
	}

	/**
	 * @param $message
	 * @param $status
	 *
	 * @return bool
	 */
	protected function log($message, $status = NULL){
		$message = "(Process ID: {$this->model->id}) " . $message;

		if (empty($status)){
			$status = AuditTrail::STATUS_SUCCESS;
		}

		return AuditTrail::log('File Import', $message, 'Media Center', $this->model->created_by,
			$status);
	}

	/**
	 * @param array $errors
	 * @param string $file_name
	 * @param null $file_type
	 *
	 * @return string
	 */
	private function _generateErrorLog(
		array $errors = [],
		string $file_name = '',
		$file_type = NULL){
		if (empty($file_type)){
			$file_type = pathinfo($file_name, PATHINFO_BASENAME);
		}

		$time      = microtime(TRUE) * 10000;
		$file_name = pathinfo($file_name, PATHINFO_FILENAME) . '_error_log_' . $time . '.csv';
		$file_path = $this->_uploadPath($file_name, FALSE, 'logs');

		$header = [
			Yii::t('common', 'Row'),
			Yii::t('common', 'Import Type'),
			Yii::t('common', 'File Name'),
			Yii::t('common', 'Message')
		];

		$file_error = fopen($file_path, 'w');

		fputcsv($file_error, $header);

		foreach ($errors as $row => $error){
			$message = $error;
			if (is_array($error)){
				if (isset($error['message'])){
					$message   = $error['message'];
					$file_type = $error['filename'] ?? $file_type;
				}else{
					$message = implode(', ', $error);
				}
			}

			$error_data = [
				$row,
				$this->model->importer,
				$file_type,
				$message
			];

			fputcsv($file_error, $error_data);
		}

		fclose($file_error);

		return $file_path;
	}

	/**
	 * @param $filename
	 * @param bool $tmp
	 * @param string $extract_folder
	 *
	 * @return string
	 */
	private function _uploadPath($filename, $tmp = FALSE, $extract_folder = '')
	: string{
		$import_field = "imports/";
		if ($tmp){
			$import_field .= 'tmp';
			if (!empty($extract_folder)){
				$import_field .= '/' . $extract_folder;
			}
		}else{
			$import_field .= strtolower($this->model->importer);
		}

		return AppHelper::uploadPath($import_field, $filename, TRUE);
	}
}
