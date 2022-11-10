<?php

namespace modules\promotion\migrations;

use yii\db\Migration;

/**
 * Class m170000_000010_update_table_promotion_turnover
 */
class m170000_000010_update_table_promotion_turnover extends Migration{

	private $_table = '{{%promotion_turnover}}';

	/**
	 * @return void
	 */
	public function up(){
		$this->addColumn($this->_table, 'round',
			$this->integer()->defaultValue(1)->after('turnover'));
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropColumn($this->_table, 'round');
	}
}