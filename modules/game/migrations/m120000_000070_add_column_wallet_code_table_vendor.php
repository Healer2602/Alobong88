<?php

namespace modules\game\migrations;

use yii\db\Migration;

/**
 * Class m120000_000070_add_column_wallet_code_table_vendor
 */
class m120000_000070_add_column_wallet_code_table_vendor extends Migration{

	private $table = '{{%vendor}}';

	/**
	 * @return bool|void
	 */
	public function up(){
		$this->addColumn($this->table, 'wallet_code', $this->integer()->after('icon'));

	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropColumn($this->table, 'wallet_code');
	}
}