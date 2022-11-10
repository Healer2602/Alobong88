<?php

namespace modules\game\migrations;

use yii\db\Migration;

/**
 * Class m160000_000000_create_table_betlog_provider
 */
class m160000_000000_create_table_betlog_provider extends Migration{

	private $_table = '{{%betlog_provider}}';

	/**
	 * @return bool|void
	 */
	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable($this->_table, [
			'id'             => $this->primaryKey(),
			'code'           => $this->string()->unique(),
			'vendor_id'      => $this->integer(),
			'product_wallet' => $this->string(),
			'status'         => $this->tinyInteger(4),
			'created_at'     => $this->integer(),
			'created_by'     => $this->integer(),
			'updated_at'     => $this->integer(),
			'updated_by'     => $this->integer(),
		], $tableOptions);

		$this->addForeignKey('fk_betlog_provider_vendor', $this->_table, 'vendor_id', '{{%vendor}}',
			'id');
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropTable($this->_table);
	}
}