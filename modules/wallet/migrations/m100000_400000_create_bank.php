<?php

namespace modules\wallet\migrations;

use yii\db\Migration;

/**
 * Class m100000_400000_create_bank
 */
class m100000_400000_create_bank extends Migration{

	/**
	 * @return bool|void
	 */
	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}
		$this->createTable('{{%bank}}', [
			'id'            => $this->primaryKey(),
			'name'          => $this->string(255),
			'logo'          => $this->text(),
			'currency_code' => $this->string(3),
			'status'        => $this->integer()->defaultValue('10'),
			'created_at'    => $this->integer(),
			'updated_at'    => $this->integer(),
			'created_by'    => $this->integer(),
			'updated_by'    => $this->integer(),
		], $tableOptions);

		$this->createIndex('idx_bank_currency_code_currency', '{{%bank}}',
			'currency_code');
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropTable('{{%bank}}');
	}
}
