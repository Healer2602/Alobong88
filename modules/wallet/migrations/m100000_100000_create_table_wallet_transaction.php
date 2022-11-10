<?php

namespace modules\wallet\migrations;

use yii\db\Migration;

/**
 * Class m100000_100000_create_table_wallet_transaction
 */
class m100000_100000_create_table_wallet_transaction extends Migration{

	/**
	 * @return bool|void|null
	 */
	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable('{{%wallet_transaction}}', [
			'id'             => $this->primaryKey(),
			'wallet_id'      => $this->integer()->notNull(),
			'transaction_id' => $this->string(255)->notNull(),
			'type'           => $this->integer()->notNull(),
			'amount'         => $this->double(2)->notNull(),
			'fee'            => $this->double(2)->defaultValue(0),
			'balance'        => $this->double(2)->notNull(),
			'gateway_id'     => $this->string(),
			'currency'       => $this->string(),
			'description'    => $this->text(),
			'note'           => $this->text(),
			'status'         => $this->integer()->notNull()->defaultValue(0),
			'reference_id'   => $this->string(255),
			'params'         => $this->text(),
			'created_at'     => $this->integer()->notNull(),
			'updated_at'     => $this->integer(),
		], $tableOptions);

		$this->createIndex('idx_wallet_transaction_wallet_customer', '{{%wallet_transaction}}',
			['wallet_id', 'type']);

		$this->addForeignKey('fk_wallet_transaction_wallet', '{{%wallet_transaction}}', 'wallet_id',
			'{{%wallet}}', 'id');
	}

	/**
	 * @return bool|void|null
	 */
	public function down(){
		$this->dropTable('{{%wallet_transaction}}');
	}
}
