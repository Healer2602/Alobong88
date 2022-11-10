<?php

namespace modules\wallet\migrations;

use yii\db\Migration;

/**
 * Class m100000_500000_create_wallet_transfer_history
 */
class m100000_500000_create_wallet_transfer_history extends Migration{

	/**
	 * @return bool|void
	 */
	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}
		$this->createTable('{{%wallet_transfer}}', [
			'id'             => $this->primaryKey(),
			'transaction_id' => $this->string(255),
			'from'           => $this->integer(),
			'to'             => $this->integer(),
			'amount'         => $this->decimal(),
			'customer_id'    => $this->integer(),
			'created_at'     => $this->integer(),
		], $tableOptions);

		$this->addForeignKey('fk_wallet_transfer_sub_wallet_from', '{{%wallet_transfer}}', 'from',
			'{{%wallet_sub}}', 'id');

		$this->addForeignKey('fk_wallet_transfer_sub_wallet_to', '{{%wallet_transfer}}', 'to',
			'{{%wallet_sub}}', 'id');

		$this->addForeignKey('fk_wallet_transfer_history_customer', '{{%wallet_transfer}}',
			'customer_id',
			'{{%customer}}', 'id');
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropTable('{{%wallet_transfer}}');
	}
}