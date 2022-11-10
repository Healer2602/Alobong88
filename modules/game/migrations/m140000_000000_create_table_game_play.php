<?php

namespace modules\game\migrations;

use yii\db\Migration;

/**
 * Class m140000_000000_create_table_game_play
 */
class m140000_000000_create_table_game_play extends Migration{

	private $_table = '{{%game_play}}';

	/**
	 * @return bool|void
	 */
	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable($this->_table, [
			'id'           => $this->primaryKey(),
			'player_id'    => $this->integer()->notNull(),
			'game_id'      => $this->integer()->notNull(),
			'product_code' => $this->string()->notNull(),
			'first_play'   => $this->integer(),
			'last_play'    => $this->integer(),
		], $tableOptions);

		$this->createIndex('idx_game_play_player_id', $this->_table, 'player_id');
		$this->createIndex('idx_game_play_game_id', $this->_table, 'game_id');
		$this->createIndex('idx_game_play_product_code', $this->_table, 'product_code');
		$this->createIndex('unq_game_play_player_product_code', $this->_table,
			['player_id', 'product_code', 'game_id'], TRUE);
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropTable($this->_table);
	}
}