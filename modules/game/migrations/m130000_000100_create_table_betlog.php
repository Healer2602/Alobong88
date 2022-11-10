<?php

namespace modules\game\migrations;

use yii\db\Migration;

/**
 * Class m130000_000100_create_table_betlog
 */
class m130000_000100_create_table_betlog extends Migration{

	private $_table = '{{%game_betlog}}';

	/**
	 * @return bool|void
	 */
	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable($this->_table, [
			'id'                => $this->primaryKey(),
			'bet_id'            => $this->string(255),
			'provider'          => $this->string(255),
			'vendor_code'       => $this->string(255),
			'game_code'         => $this->string(255),
			'player_id'         => $this->string(255),
			'amount'            => $this->float(),
			'valid_amount'      => $this->float(),
			'bonus'             => $this->float(),
			'turnover_bonus'    => $this->float(),
			'turnover_wo_bonus' => $this->float(),
			'total_rebate'      => $this->float(),
			'params'            => $this->string(),
			'status'            => $this->integer(),
			'created_at'        => $this->integer(),
			'updated_at'        => $this->integer(),
		], $tableOptions);

		$this->createIndex('unq_game_betlog_bet', $this->_table,
			['bet_id', 'provider', 'player_id'], TRUE);

		$this->createIndex('idx_game_betlog_vendor', $this->_table, 'vendor_code');
		$this->createIndex('idx_game_betlog_player', $this->_table, 'player_id');
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropTable($this->_table);
	}
}