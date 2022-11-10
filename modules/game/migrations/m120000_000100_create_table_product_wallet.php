<?php

namespace modules\game\migrations;

use yii\db\Migration;

/**
 * Class m120000_000100_create_table_product_wallet
 */
class m120000_000100_create_table_product_wallet extends Migration{

	private $_table = '{{%product_wallet}}';

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
			'code'       => $this->string()->notNull(),
			'type_id'    => $this->integer(),
			'vendor_id'  => $this->integer(),
			'status'     => $this->tinyInteger(4),
			'created_at' => $this->integer(),
			'created_by' => $this->integer(),
			'updated_at' => $this->integer(),
			'updated_by' => $this->integer(),
		], $tableOptions);

		$this->addForeignKey('fk_product_wallet_type', $this->_table, 'type_id', '{{%game_type}}',
			'id');

		$this->addForeignKey('fk_product_wallet_vendor', $this->_table, 'vendor_id', '{{%vendor}}',
			'id');

		$this->createIndex('unq_product_wallet_type_vendor', $this->_table,
			['type_id', 'vendor_id'], TRUE);
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropTable($this->_table);
	}
}