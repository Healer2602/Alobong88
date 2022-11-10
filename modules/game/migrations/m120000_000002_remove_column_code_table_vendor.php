<?php

namespace modules\game\migrations;

use yii\db\Migration;

/**
 * Class m120000_000002_remove_column_code_table_vendor
 */
class m120000_000002_remove_column_code_table_vendor extends Migration{

	private $table = '{{%vendor}}';

	/**
	 * @return bool|void
	 */
	public function up(){
		$this->dropColumn($this->table, 'code');
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->addColumn($this->table, 'code', $this->string());
	}
}