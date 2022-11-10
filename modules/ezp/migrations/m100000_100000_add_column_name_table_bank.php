<?php

namespace modules\ezp\migrations;

use yii\db\Migration;

/**
 * Class m100000_100000_add_column_name_table_bank
 */
class m100000_100000_add_column_name_table_bank extends Migration{

	private $_table = '{{%ezp_bank}}';

	/**
	 * @return void
	 */
	public function up(){
		$this->addColumn($this->_table, 'name', $this->text()->after('logo'));
	}

	/**
	 * @return void
	 */
	public function down(){
		$this->dropColumn($this->_table, 'name');
	}
}
