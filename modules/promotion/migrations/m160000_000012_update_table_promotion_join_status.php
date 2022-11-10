<?php

namespace modules\promotion\migrations;

use yii\db\Migration;

/**
 * Class m160000_000011_update_table_promotion_join_rate
 */
class m160000_000012_update_table_promotion_join_status extends Migration{

	private $_table = '{{%promotion_joining}}';

	/**
	 * @return void
	 */
	public function up(){
		$this->addColumn($this->_table, 'status', $this->tinyInteger()->defaultValue(0));
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropColumn($this->_table, 'status');
	}
}