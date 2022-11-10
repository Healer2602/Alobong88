<?php

namespace modules\game\migrations;

use yii\db\Migration;

/**
 * Class m120000_000050_add_column_table_type
 */
class m120000_000050_add_column_table_type extends Migration{

	private $table = '{{%game_type}}';

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