<?php

namespace common\base;

use Mpdf\HTMLParserMode;
use Mpdf\Output\Destination;

/**
 * Class Mpdf
 *
 * @package common\base
 */
class Mpdf extends \Mpdf\Mpdf{

	/**
	 * Mpdf constructor.
	 *
	 * @param array $config
	 *
	 * @throws \Mpdf\MpdfException
	 */
	public function __construct(array $config = []){
		$config['mode']    = $config['mode'] ?? 'utf-8';
		$config['format']  = $config['format'] ?? 'A4';
		$config['tempDir'] = AppHelper::uploadPath(TRUE, 'mpdf');

		parent::__construct($config);
	}

	/**
	 * @param $content
	 * @param $filename
	 *
	 * @throws \Mpdf\MpdfException
	 */
	public function download($content, $filename){
		$this->WriteHTML($content, HTMLParserMode::HTML_BODY);
		$this->Output($filename, Destination::DOWNLOAD);
		exit();
	}
}