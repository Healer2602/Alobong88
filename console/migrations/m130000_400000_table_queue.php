<?php

use yii\db\Migration;

/**
 * Class m130000_400000_table_queue
 */
class m130000_400000_table_queue extends Migration{

	protected $table_name = '{{%queue}}';

	/**
	 * @return void
	 */
	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable($this->table_name, [
			'id'          => $this->primaryKey(),
			'channel'     => $this->string()->notNull(),
			'job'         => $this->text()->notNull(),
			'pushed_at'   => $this->integer()->notNull(),
			'ttr'         => $this->integer()->notNull(),
			'delay'       => $this->integer()->notNull(),
			'priority'    => $this->integer()->unsigned()->notNull()->defaultValue(1024),
			'reserved_at' => $this->integer(),
			'attempt'     => $this->integer(),
			'done_at'     => $this->integer(),
		], $tableOptions);

		$this->createIndex('idx_queue_channel', $this->table_name, 'channel');
		$this->createIndex('idx_queue_reserved_at', $this->table_name, 'reserved_at');
		$this->createIndex('idx_queue_priority', $this->table_name, 'priority');
	}

	/**
	 * @return void
	 */
	public function down(){
		$this->dropTable($this->table_name);
	}
}