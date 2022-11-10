<?php

namespace modules\game\migrations;

use yii\db\Migration;

/**
 * Class m120000_000060_add_column_table_vendor
 */
class m120000_000060_add_column_table_vendor extends Migration{

	private $table = '{{%vendor}}';

	/**
	 * @return bool|void
	 */
	public function up(){
		$this->addColumn($this->table, 'ordering', $this->integer()->after('status'));

	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropColumn($this->table, 'ordering');
	}
}