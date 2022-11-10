<?php

namespace modules\internet_banking\migrations;

use yii\db\Migration;

/**
 * Class m100000_000020_update_bank_link
 */
class m100000_000020_update_bank_link extends Migration{

	private $_table = '{{%internet_banking_bank}}';

	/**
	 * @return void
	 */
	public function up(){
		$this->addColumn($this->_table, 'bank_id', $this->integer()->after('id'));
		$this->dropColumn($this->_table, 'name');
		$this->addForeignKey('fk_internet_bank_bank', $this->_table, 'bank_id', '{{%bank}}', 'id');
	}

	/**
	 * @return void
	 */
	public function down(){
		$this->dropForeignKey('fk_internet_bank_bank', $this->_table);
		$this->addColumn($this->_table, 'name', $this->string()->after('id'));
		$this->dropColumn($this->_table, 'bank_id');
	}
}
