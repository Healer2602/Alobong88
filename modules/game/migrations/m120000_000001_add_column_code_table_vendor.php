<?php

namespace modules\game\migrations;

use yii\db\Migration;

/**
 * Class m120000_000001_add_column_code_table_vendor
 */
class m120000_000001_add_column_code_table_vendor extends Migration{

	private $table = '{{%vendor}}';

	/**
	 * @return bool|void
	 */
	public function up(){
		$this->addColumn($this->table, 'code', $this->string()->after('status'));

		$this->createIndex('idx_vendor_code', $this->table, 'code');
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropColumn($this->table, 'code');
	}
}