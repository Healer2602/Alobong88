<?php

namespace modules\media_center\base;


/**
 * Interface of import helper
 */
interface BaseImporterInterface{

	/**
	 * @return mixed
	 */
	public function execute();

	/**
	 * @return mixed
	 */
	public function extractData();
}