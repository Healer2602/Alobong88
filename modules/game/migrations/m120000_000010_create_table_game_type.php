<?php

namespace modules\game\migrations;

use yii\db\Migration;

/**
 * Class m120000_000010_create_table_game_type
 */
class m120000_000010_create_table_game_type extends Migration{

	/**
	 * @return bool|void
	 */
	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable('{{%game_type}}', [
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
		$this->dropTable('{{%game_type}}');
	}
}