<?php

namespace modules\game\migrations;

use yii\db\Migration;

/**
 * Class m150000_000000_create_table_vendor_content
 */
class m150000_000000_create_table_vendor_content extends Migration{

	private $_table = '{{%vendor_content}}';

	/**
	 * @return bool|void
	 */
	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable($this->_table, [
			'id'         => $this->primaryKey(),
			'name'       => $this->string()->notNull(),
			'icon'       => $this->text(),
			'type_id'    => $this->integer(),
			'vendor_id'  => $this->integer(),
			'status'     => $this->tinyInteger(4),
			'created_at' => $this->integer(),
			'created_by' => $this->integer(),
			'updated_at' => $this->integer(),
			'updated_by' => $this->integer(),
		], $tableOptions);

		$this->addForeignKey('fk_vendor_content_type', $this->_table, 'type_id', '{{%game_type}}',
			'id');

		$this->addForeignKey('fk_vendor_content_vendor', $this->_table, 'vendor_id', '{{%vendor}}',
			'id');

		$this->createIndex('unq_vendor_content_type_vendor', $this->_table,
			['type_id', 'vendor_id'], TRUE);
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropTable($this->_table);
	}
}