<?php

namespace modules\wallet\migrations;

use yii\db\Migration;

/**
 * Class m100000_200200_create_table_gateway_customer_rank
 */
class m100000_200200_create_table_gateway_customer_rank extends Migration{

	/**
	 * @return bool|void|null
	 */
	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable('{{%wallet_gateway_customer_rank}}', [
			'wallet_gateway_id' => $this->integer()->notNull(),
			'customer_rank_id'  => $this->integer()->notNull(),
		], $tableOptions);

		$this->addPrimaryKey('pk_wallet_gateway_customer_rank', '{{%wallet_gateway_customer_rank}}',
			['wallet_gateway_id', 'customer_rank_id']);
	}

	/**
	 * @return bool|void|null
	 */
	public function down(){
		$this->dropTable('{{%wallet_gateway_customer_rank}}');
	}
}
