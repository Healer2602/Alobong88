<?php

namespace common\base;

/**
 * Class Queue
 *
 * @package common\base
 */
class Queue extends \yii\queue\db\Queue{

	const CHANNEL_FILE = 'file';

	public $tableName = '{{%queue}}';

	/**
	 * @inheritdoc
	 *
	 * Using this one cause the core function is having issue
	 */
	protected function pushMessage($message, $ttr, $delay, $priority){
		$this->db->createCommand()->insert($this->tableName, [
			'job'       => $message,
			'pushed_at' => time(),
			'ttr'       => $ttr,
			'delay'     => $delay,
			'priority'  => $priority ?: 1024,
			'channel'   => $this->channel
		])->execute();

		$table = $this->db->getTableSchema($this->tableName);

		return $this->db->getLastInsertID($table->sequenceName);
	}
}