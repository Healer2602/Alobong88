<?php

namespace modules\game\migrations;

use yii\db\Migration;

/**
 * Class m120000_000001_add_column_table_vendor
 */
class m120000_000001_add_column_table_vendor extends Migration{

	private $table = '{{%vendor}}';

	/**
	 * @return bool|void
	 */
	public function up(){
		$this->addColumn($this->table, 'slug', $this->string()->unique()->after('name'));

	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropColumn($this->table, 'slug');
	}
}