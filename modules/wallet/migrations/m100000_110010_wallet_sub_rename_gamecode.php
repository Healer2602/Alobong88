<?php

namespace modules\wallet\migrations;

use yii\db\Migration;

/**
 * Class m100000_110010_wallet_sub_rename_gamecode
 */
class m100000_110010_wallet_sub_rename_gamecode extends Migration{

	private $_table = '{{%wallet_sub}}';

	/**
	 * @return bool|void|null
	 */
	public function up(){
		$this->renameColumn($this->_table, 'game_code', 'product_code');
		$this->createIndex('idx_wallet_sub_product_code', $this->_table, 'product_code');

		$this->createIndex('unq_wallet_sub_product', $this->_table, ['wallet_id', 'product_code'],
			TRUE);
	}

	/**
	 * @return bool|void|null
	 */
	public function down(){
		$this->dropIndex('idx_wallet_sub_product_code', $this->_table);
		$this->renameColumn($this->_table, 'product_code', 'game_code');
	}
}
