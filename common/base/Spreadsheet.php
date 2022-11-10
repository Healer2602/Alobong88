<?php

namespace common\base;

use Closure;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet as BaseSpreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use Yii;
use yii\base\Widget;
use yii\db\Query;
use yii\helpers\ArrayHelper;


/**
 * @property \yii\db\ActiveRecord $model
 * @property \yii\db\ActiveQuery|Query $data
 */
class Spreadsheet extends Widget{

	const FILE_FORMATS = ['xlsx', 'csv', 'pdf'];

	const SERIAL_FIELD = '__serial';

	public $file_name;
	public $data;
	public $orientation = 'L';
	public $title = '';
	public $attributes;
	public $labels;
	public $serial = FALSE;
	public $headers = [];
	public $attachment = FALSE;
	public $modelClass;
	public $destination_path = NULL;

	protected $file_format;
	protected $content_type;
	protected $model;

	/**
	 * @return mixed|void
	 * @throws \Exception
	 * @throws \PhpOffice\PhpSpreadsheet\Exception
	 * @throws \PhpOffice\PhpSpreadsheet\Style\Exception
	 */
	public function run(){
		if (!($data = $this->data) || !($this->data instanceof Query)){
			return;
		}

		if (!$attributes = $this->attributes){
			return;
		}

		$this->model = $this->data->modelClass ?? $this->modelClass ?? NULL;

		if (empty($this->model)){
			return;
		}

		$this->model = new $this->model ();

		$this->_preprocessAttributes($attributes);

		$header = ArrayHelper::getColumn($attributes, 'label', FALSE);
		$fields = ArrayHelper::getColumn($attributes, 'field', FALSE);

		$spreadsheet = new BaseSpreadsheet();
		$spreadsheet->setActiveSheetIndex(0);

		$current_row = 1;

		if (!empty($this->headers)){
			foreach ($this->headers as $cell => $value){
				$spreadsheet->getActiveSheet()->setCellValue($cell, $value);
			}

			$current_row = $spreadsheet->getActiveSheet()->getHighestRow() + 2;
		}

		$spreadsheet->getActiveSheet()->fromArray($header, '', 'A' . $current_row);
		$highestColumn = $spreadsheet->getActiveSheet()->getHighestColumn();

		$this->_getFileType();

		if ($this->file_format != 'csv'){

			$spreadsheet->getDefaultStyle()->applyFromArray([
				'borders' => [
					'allBorders' => [
						'borderStyle' => Border::BORDER_THIN,
						'color'       => ['rgb' => '555555'],
					]
				]
			]);

			for ($i = 'A'; $i <= $highestColumn; $i ++){
				$spreadsheet->getActiveSheet()->getStyle($i . $current_row)->applyFromArray([
					'fill'    => [
						'fillType'   => Fill::FILL_SOLID,
						'startColor' => ['rgb' => '00ab5e']
					],
					'font'    => [
						'bold'  => TRUE,
						'color' => [
							'rgb' => 'FFFFFF'
						]
					],
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color'       => ['rgb' => '555555'],
						]
					]
				]);
			}
		}

		/**@var \yii\db\ActiveRecord[]|array $data */
		$data = $this->data->all();

		foreach ($data as $i => $datum){
			$row_id   = $current_row + $i + 1;
			$row_data = [];
			foreach ($fields as $field){
				if ($field == self::SERIAL_FIELD){
					$row_data[] = $i + 1;
				}elseif (is_array($datum) && is_string($field)){
					$row_data[] = $datum[$field] ?? '';
				}elseif ($field instanceof Closure){
					$row_data[] = call_user_func($field, $datum);
				}elseif (is_string($field) && $datum->hasAttribute($field)){
					$row_data[] = $datum->getAttribute($field);
				}else{
					$row_data[] = '';
				}
			}

			$spreadsheet->getActiveSheet()->fromArray($row_data, '', 'A' . $row_id);
		}

		for ($i = 'A'; $i <= $highestColumn; $i ++){
			$spreadsheet->getActiveSheet()
			            ->getColumnDimension($i)
			            ->setAutoSize(TRUE);
		}

		if ($this->title){
			$spreadsheet->getActiveSheet()->setTitle($this->title);
		}

		$spreadsheet->getActiveSheet()
		            ->getPageSetup()
		            ->setOrientation($this->orientation);
		$spreadsheet->getActiveSheet()
		            ->getPageSetup()
		            ->setPaperSize(PageSetup::PAPERSIZE_A4);
		$spreadsheet->getProperties()
		            ->setCreator(Yii::$app->name)
		            ->setLastModifiedBy(Yii::$app->name)
		            ->setTitle($this->title)
		            ->setSubject($this->title);

		$spreadsheet->setActiveSheetIndex(0);

		if ($this->attachment){
			return $this->_writeFile($spreadsheet);
		}

		$this->_export($spreadsheet);
	}

	/**
	 * @param \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet
	 *
	 * @throws \Exception
	 */
	private function _export(BaseSpreadsheet $spreadsheet){
		$writer_name = ucfirst($this->file_format);

		if (strtolower($writer_name) == 'pdf'){
			IOFactory::registerWriter('Pdf', Mpdf::class);
		}

		header('Content-Type: ' . $this->content_type);
		header('Content-Disposition: attachment;filename="' . $this->file_name . '"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');

		// If you're serving to IE over SSL, then the following may be needed
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
		header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header('Pragma: public'); // HTTP/1.0

		$writer = IOFactory::createWriter($spreadsheet, $writer_name);
		$writer->save('php://output');
		exit;
	}

	/**
	 * @param \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet
	 *
	 * @return bool|string
	 * @throws \Exception
	 */
	private function _writeFile(BaseSpreadsheet $spreadsheet){
		$writer_name = ucfirst($this->file_format);

		if (strtolower($writer_name) == 'pdf'){
			IOFactory::registerWriter('Pdf', Mpdf::class);
		}

		$writer = IOFactory::createWriter($spreadsheet, $writer_name);
		if (empty($this->destination_path)){
			$this->destination_path = Yii::getAlias('@private_files');
		}

		$destination_path = rtrim($this->destination_path);
		$file_path        = $destination_path . '/' . $this->file_name;
		$writer->save($file_path);

		return $file_path;
	}

	/**
	 *
	 */
	private function _getFileType(){
		if (!$file_name = $this->file_name){
			return;
		}

		$file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

		if (!in_array($file_type, self::FILE_FORMATS)){
			return;
		}

		switch ($file_type){
			case 'csv':
				$content_type = 'text/csv';
				break;
			case 'pdf':
				$content_type = 'application/pdf';
				break;
			default:
				$content_type = 'application/vnd.ms-excel';
		}

		$this->content_type = $content_type;
		$this->file_format  = $file_type;
	}

	/**
	 * @param array $attributes
	 */
	private function _preprocessAttributes(array &$attributes){
		if (empty($attributes)){
			return;
		}

		foreach ($attributes as $key => &$attribute){
			if (is_string($attribute)){
				$field_name = $attribute;
				$attribute  = [
					'field' => $field_name,
					'label' => $this->labels[$field_name] ?? $this->model->getAttributeLabel($field_name)
				];
			}elseif ($attribute instanceof Closure){
				$attribute = [
					'field' => $attribute,
					'label' => $this->labels[$key] ?? $this->model->getAttributeLabel($key),
				];
			}else{
				unset($attribute);
			}
		}

		if (!empty($this->serial)){
			array_unshift($attributes, ['field' => self::SERIAL_FIELD, 'label' => '#']);
		}
	}

}