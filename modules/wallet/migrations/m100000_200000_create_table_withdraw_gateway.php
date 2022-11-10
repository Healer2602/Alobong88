<?php

namespace modules\wallet\migrations;

use common\base\Status;
use yii\db\Migration;

/**
 * Class m100000_200000_create_table_withdraw_gateway
 */
class m100000_200000_create_table_withdraw_gateway extends Migration{

	/**
	 * @return bool|void|null
	 */
	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable('{{%wallet_gateway}}', [
			'id'         => $this->primaryKey(),
			'title'      => $this->string()->notNull(),
			'key'        => $this->string()->notNull(),
			'type'       => $this->tinyInteger()->notNull()->defaultValue(1),
			'currency'   => $this->string()->notNull(),
			'fee'        => $this->double()->notNull()->defaultValue(0),
			'is_sandbox' => $this->tinyInteger(1)->notNull()->defaultValue(0),
			'api_key'    => $this->string()->notNull(),
			'api_secret' => $this->string()->notNull(),
			'status'     => $this->integer()->defaultValue(Status::STATUS_ACTIVE),
			'ordering'   => $this->integer(),
			'created_at' => $this->integer(),
			'created_by' => $this->integer(),
			'updated_at' => $this->integer(),
			'updated_by' => $this->integer(),
		], $tableOptions);

		$this->createIndex('unq_wallet_gateway', '{{%wallet_gateway}}', ['key', 'type'], TRUE);
	}

	/**
	 * @return bool|void|null
	 */
	public function down(){
		$this->dropTable('{{%wallet_gateway}}');
	}
}
