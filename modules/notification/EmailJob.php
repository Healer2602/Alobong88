<?php

namespace modules\notification;

use Exception;
use yii\base\BaseObject;
use yii\queue\RetryableJobInterface;

/**
 * Class EmailJob
 *
 * @package modules\notification
 */
class EmailJob extends BaseObject implements RetryableJobInterface{

	public $to;
	public $subject;
	public $body;
	public $body_plain = '';
	public $attachment = NULL;
	public $reply_to = '';
	public $ccs = [];
	public $bccs = [];

	/**
	 * @param EmailJob $queue which pushed and is handling the job
	 *
	 * @return void|mixed result of the job execution
	 * @throws \Exception
	 */
	public function execute($queue){
		$sent = Mailer::send($this->to, $this->subject, $this->body, $this->body_plain,
			$this->attachment, $this->reply_to, $this->ccs, $this->bccs);

		if (!$sent){
			throw new Exception("Email cannot be sent.");
		}
	}

	/**
	 * @return int time to reserve in seconds
	 */
	public function getTtr(){
		return 2 * 60;
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