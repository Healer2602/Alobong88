<?php

namespace modules\ezp\migrations;

use yii\db\Migration;

/**
 * Class m100000_000022_update_bank_link
 */
class m100000_000022_update_bank_link extends Migration{

	private $_table = '{{%ezp_bank}}';

	/**
	 * @return void
	 */
	public function up(){
		$this->addColumn($this->_table, 'bank_id', $this->integer()->after('id'));
		$this->dropColumn($this->_table, 'name');
		$this->addForeignKey('fk_ezp_bank_bank', $this->_table, 'bank_id', '{{%bank}}', 'id');
	}

	/**
	 * @return void
	 */
	public function down(){
		$this->dropForeignKey('fk_ezp_bank_bank', $this->_table);
		$this->addColumn($this->_table, 'name', $this->string()->after('id'));
		$this->dropColumn($this->_table, 'bank_id');
	}
}
