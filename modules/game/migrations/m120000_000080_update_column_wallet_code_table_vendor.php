<?php

namespace modules\game\migrations;

use yii\db\Migration;

/**
 * Class m120000_000080_update_column_wallet_code_table_vendor
 */
class m120000_000080_update_column_wallet_code_table_vendor extends Migration{

	private $table = '{{%vendor}}';

	/**
	 * @return bool|void
	 */
	public function up(){
		$this->alterColumn($this->table, 'wallet_code', $this->string()->unique());
	}

	/**
	 * @return bool|void
	 */
	public function down(){
	}
}