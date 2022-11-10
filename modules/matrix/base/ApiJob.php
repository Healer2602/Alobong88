<?php

namespace modules\matrix\base;

use Exception;
use yii\base\BaseObject;
use yii\queue\RetryableJobInterface;

/**
 * Class ApiJob
 *
 * @package modules\matrix
 */
class ApiJob extends BaseObject implements RetryableJobInterface{

	public $function_name;
	public $data;
	public $method_post = TRUE;

	/**
	 * @param ApiJob $queue which pushed and is handling the job
	 *
	 * @return void|mixed result of the job execution
	 * @throws \Exception
	 */
	public function execute($queue){
		if ($this->method_post){
			$response = API::post($this->function_name, $this->data);
		}else{
			$response = API::get($this->function_name, $this->data);
		}

		if (empty($response)){
			throw new Exception("API error");
		}
	}

	/**
	 * @return int time to reserve in seconds
	 */
	public function getTtr(){
		return 1 * 60;
	}

	/**
	 * @param int $attempt number
	 * @param \Exception|\Throwable $error from last execute of the job
	 *
	 * @return bool
	 */
	public function canRetry($attempt, $error){
		return $attempt < 11;
	}
}