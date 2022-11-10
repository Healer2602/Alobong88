<?php

namespace modules\internet_banking\migrations;

use yii\db\Migration;

/**
 * Class m100000_100000_add_column_name_table_internet_banking_bank
 */
class m100000_100000_add_column_name_table_internet_banking_bank extends Migration{

	private $_table = '{{%internet_banking_bank}}';

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
