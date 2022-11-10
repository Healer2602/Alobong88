<?php

namespace modules\game\migrations;

use yii\db\Migration;

/**
 * Class m120000_000000_create_table_vendor
 */
class m120000_000000_create_table_vendor extends Migration{

	/**
	 * @return bool|void
	 */
	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable('{{%vendor}}', [
			'id'         => $this->primaryKey(),
			'name'       => $this->string(),
			'icon'       => $this->text(),
			'status'     => $this->tinyInteger(4),
			'created_at' => $this->integer(),
			'created_by' => $this->integer(),
			'updated_at' => $this->integer(),
			'updated_by' => $this->integer(),
		], $tableOptions);

	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropTable('{{%vendor}}');
	}
}