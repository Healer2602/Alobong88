<?php

namespace modules\wallet\migrations;

use yii\db\Migration;

/**
 * Class m100000_110100_create_table_wallet_sub_transaction
 */
class m100000_110100_create_table_wallet_sub_transaction extends Migration{

	/**
	 * @return bool|void|null
	 */
	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable('{{%wallet_sub_transaction}}', [
			'id'             => $this->primaryKey(),
			'wallet_sub_id'  => $this->integer()->notNull(),
			'transaction_id' => $this->string(255)->notNull(),
			'type'           => $this->integer()->notNull(),
			'amount'         => $this->double(2)->notNull(),
			'fee'            => $this->double(2)->defaultValue(0),
			'balance'        => $this->double(2)->notNull(),
			'currency'       => $this->string(),
			'description'    => $this->text(),
			'note'           => $this->text(),
			'status'         => $this->integer()->notNull()->defaultValue(0),
			'reference_id'   => $this->string(255),
			'params'         => $this->text(),
			'created_at'     => $this->integer()->notNull(),
			'updated_at'     => $this->integer(),
		], $tableOptions);

		$this->createIndex('idx_wallet_sub_transaction_wallet_customer',
			'{{%wallet_sub_transaction}}', ['wallet_sub_id', 'type']);

		$this->addForeignKey('fk_wallet_sub_transaction_wallet', '{{%wallet_sub_transaction}}',
			'wallet_sub_id', '{{%wallet_sub}}', 'id');
	}

	/**
	 * @return bool|void|null
	 */
	public function down(){
		$this->dropTable('{{%wallet_sub_transaction}}');
	}
}
