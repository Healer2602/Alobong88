<?php

namespace modules\promotion\migrations;

use yii\db\Migration;

/**
 * Class m160000_000011_update_table_promotion_join_rate
 */
class m160000_000011_update_table_promotion_join_rate extends Migration{

	private $_table = '{{%promotion_joining}}';

	/**
	 * @return void
	 */
	public function up(){
		$this->addColumn($this->_table, 'rate', $this->float()->after('wallet_code'));
		$this->addColumn($this->_table, 'bonus', $this->float()->after('rate'));
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropColumn($this->_table, 'rate');
		$this->dropColumn($this->_table, 'bonus');
	}
}