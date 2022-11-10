<?php

namespace modules\game\migrations;

use yii\db\Migration;

/**
 * Class m130000_000110_update_table_betlog
 */
class m130000_000110_update_table_betlog extends Migration{

	private $_table = '{{%game_betlog}}';

	/**
	 * @return void
	 */
	public function up(){
		$this->dropIndex('idx_game_betlog_vendor', $this->_table);
		$this->dropColumn($this->_table, 'vendor_code');

		$this->createIndex('idx_game_betlog_provider', $this->_table, 'provider');

		$this->addColumn($this->_table, 'wallet_code',
			$this->string()->notNull()->after('game_code'));
		$this->createIndex('idx_game_betlog_wallet_code', $this->_table, 'wallet_code');
		$this->addColumn($this->_table, 'turnover', $this->float()->after('bonus'));
	}
}