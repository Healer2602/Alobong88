<?php

namespace modules\wallet\migrations;

use common\base\Status;
use yii\db\Migration;

/**
 * Class m100000_100000_create_table_wallet
 */
class m100000_100000_create_table_wallet extends Migration{

	/**
	 * @return void
	 */
	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable('{{%wallet}}', [
			'id'               => $this->primaryKey(),
			'customer_id'      => $this->integer()->notNull(),
			'balance'          => $this->double(2)->defaultValue(0),
			'turnover'         => $this->double()->unsigned()->notNull()->defaultValue(0),
			'previous_balance' => $this->double(2)->defaultValue(0),
			'last_update'      => $this->integer(),
			'verify_hash'      => $this->string()->notNull(),
			'status'           => $this->integer()->defaultValue(Status::STATUS_ACTIVE),
		], $tableOptions);

		$this->createIndex('idx_wallet_customer', '{{%wallet}}', 'customer_id');
	}

	/**
	 * @return void
	 */
	public function down(){
		$this->dropTable('{{%wallet}}');
	}
}
