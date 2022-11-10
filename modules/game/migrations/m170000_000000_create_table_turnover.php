<?php

namespace modules\game\migrations;

use yii\db\Migration;

/**
 * Class m170000_000000_create_table_turnover
 */
class m170000_000000_create_table_turnover extends Migration{

	private $_table = '{{%game_turnover}}';

	/**
	 * @return void
	 */
	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable($this->_table, [
			'id'          => $this->primaryKey(),
			'player_id'   => $this->string()->notNull(),
			'wallet_code' => $this->string()->notNull(),
			'date'        => $this->date()->notNull(),
			'turnover'    => $this->decimal(10, 3)->defaultValue(0),
			'created_at'  => $this->integer(),
			'updated_at'  => $this->integer(),
		], $tableOptions);

		$this->createIndex('idx_game_turnover_wallet_code', $this->_table, 'wallet_code');
		$this->createIndex('idx_game_turnover_player', $this->_table, 'player_id');

		$this->createIndex('idx_game_turnover_day', $this->_table,
			['player_id', 'wallet_code', 'date'], TRUE);
	}

	/**
	 * @return void
	 */
	public function down(){
		$this->dropTable($this->_table);
	}
}