<?php

namespace modules\promotion\migrations;

use yii\db\Migration;

/**
 * Class m170000_000015_update_table_promotion_win
 */
class m170000_000015_update_table_promotion_win extends Migration{

	private $_table = '{{%promotion_turnover}}';

	/**
	 * @return void
	 */
	public function up(){
		$this->addColumn($this->_table, 'win',
			$this->float()->defaultValue(0)->after('turnover'));
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropColumn($this->_table, 'win');
	}
}