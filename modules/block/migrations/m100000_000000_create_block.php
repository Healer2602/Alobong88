<?php

namespace modules\block\migrations;

use yii\db\Migration;

/**
 * Class m100000_000000_create_block
 */
class m100000_000000_create_block extends Migration{

	private $_table_name = '{{%block}}';

	/**
	 * @return bool|void
	 */
	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable($this->_table_name, [
			'id'         => $this->primaryKey(),
			'name'       => $this->string()->notNull(),
			'title'      => $this->string(),
			'content'    => $this->text(),
			'setting'    => $this->text(),
			'position'   => $this->string(),
			'type'       => $this->string()->notNull()->defaultValue('html'),
			'ordering'   => $this->integer()->notNull()->defaultValue(1),
			'status'     => $this->integer()->notNull()->defaultValue('10'),
			'language'   => $this->string(2),
			'created_by' => $this->integer()->notNull(),
			'created_at' => $this->integer()->notNull(),
			'updated_by' => $this->integer(),
			'updated_at' => $this->integer(),
		], $tableOptions);

		$this->createIndex('idx_block_position', $this->_table_name, 'position');
		$this->createIndex('idx_block_language', $this->_table_name, 'language');
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropTable($this->_table_name);
	}
}
