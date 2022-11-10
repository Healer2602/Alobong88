<?php

namespace modules\promotion\migrations;

use yii\db\Migration;

/**
 * Class m170000_000000_create_table_promotion_turnover
 */
class m170000_000000_create_table_promotion_turnover extends Migration{

	private $_table = '{{%promotion_turnover}}';

	/**
	 * @return void
	 */
	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable($this->_table, [
			'id'                   => $this->primaryKey(),
			'player_id'            => $this->string()->notNull(),
			'promotion_joining_id' => $this->integer()->notNull(),
			'wallet_code'          => $this->string()->notNull(),
			'date'                 => $this->date()->notNull(),
			'turnover'             => $this->decimal(10, 3)->defaultValue(0),
			'created_at'           => $this->integer(),
			'updated_at'           => $this->integer(),
		], $tableOptions);

		$this->createIndex('idx_promotion_turnover_wallet_code', $this->_table, 'wallet_code');
		$this->createIndex('idx_promotion_turnover_player', $this->_table, 'player_id');
		$this->createIndex('idx_promotion_turnover_promotion_joining', $this->_table,
			'promotion_joining_id');

		$this->createIndex('idx_promotion_turnover_day', $this->_table,
			['promotion_joining_id', 'date', 'wallet_code'], TRUE);
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropTable($this->_table);
	}
}