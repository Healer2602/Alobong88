<?php

namespace modules\promotion\migrations;

use yii\db\Migration;

/**
 * Class m160000_000010_update_table_promotion_join_wallet_code
 */
class m160000_000010_update_table_promotion_join_wallet_code extends Migration{

	private $_table = '{{%promotion_joining}}';

	/**
	 * @return void
	 */
	public function up(){
		$this->addColumn($this->_table, 'wallet_code', $this->string()->after('wallet_id'));
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropColumn($this->_table, 'wallet_code');
	}
}