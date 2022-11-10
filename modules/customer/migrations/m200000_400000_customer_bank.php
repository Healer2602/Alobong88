<?php

namespace modules\customer\migrations;

use yii\db\Migration;

/**
 * Class m200000_400000_customer_bank
 */
class m200000_400000_customer_bank extends Migration{

	/**
	 * @return bool|void
	 */
	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable('{{%customer_bank}}', [
			'id'             => $this->primaryKey(),
			'customer_id'    => $this->integer()->notNull(),
			'bank_id'        => $this->integer()->notNull(),
			'account_id'     => $this->string(),
			'account_name'   => $this->string(),
			'account_branch' => $this->string(),
		], $tableOptions);

		$this->addForeignKey('fk_customer_bank_customer', '{{%customer_bank}}', 'customer_id',
			'{{%customer}}', 'id');

		$this->createIndex('idx_customer_bank_bank', '{{%customer_bank}}', 'bank_id');
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropTable('{{%customer_bank}}');
	}
}
