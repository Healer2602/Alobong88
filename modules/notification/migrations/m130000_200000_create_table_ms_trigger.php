<?php

namespace modules\notification\migrations;

use yii\db\Migration;

/**
 * Class m130000_200000_create_table_ms_trigger
 */
class m130000_200000_create_table_ms_trigger extends Migration{

	/**
	 * @return bool|void
	 */
	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable('{{%trigger}}', [
			'id'     => $this->primaryKey(),
			'key'    => $this->string(255)->unique()->notNull(),
			'name'   => $this->string(1000)->notNull(),
			'level'  => $this->string(255),
			'params' => $this->string(1000)
		], $tableOptions);
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropTable('{{%trigger}}');
	}
}
