<?php

namespace modules\game\migrations;

use yii\db\Migration;

/**
 * Class m120000_000051_add_column_layout_table_type
 */
class m120000_000051_add_column_layout_table_type extends Migration{

	private $table = '{{%game_type}}';

	/**
	 * @return bool|void
	 */
	public function up(){
		$this->addColumn($this->table, 'layout', $this->string()->after('ordering'));
		$this->addColumn($this->table, 'slug', $this->string()->unique()->after('name'));
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropColumn($this->table, 'layout');
		$this->dropColumn($this->table, 'slug');
	}
}