<?php

namespace modules\game\migrations;

use yii\db\Migration;

/**
 * Class m120000_000081_remove_wallet_code_table_vendor
 */
class m120000_000081_remove_wallet_code_table_vendor extends Migration{

	private $table = '{{%vendor}}';

	/**
	 * @return bool|void
	 */
	public function up(){
		$this->dropColumn($this->table, 'wallet_code');
	}

	/**
	 * @return bool|void
	 */
	public function down(){
	}
}