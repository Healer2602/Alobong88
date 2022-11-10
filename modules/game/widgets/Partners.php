<?php

namespace modules\game\widgets;

/**
 * Class Vendors
 *
 * @package modules\game\widgets
 */
class Partners extends GamesVendor{

	public $limit = - 1;

	/**
	 * @return string
	 */
	public function run(){
		if ($data = $this->findVendors()){
			return $this->render('vendors', [
				'data' => $data
			]);
		}

		return '';
	}
}