<?php

namespace modules\wallet\migrations;

use common\base\Status;
use yii\db\Migration;

/**
 * Class m100000_110000_create_table_wallet_sub
 */
class m100000_110000_create_table_wallet_sub extends Migration{

	/**
	 * @return bool|void|null
	 */
	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable('{{%wallet_sub}}', [
			'id'          => $this->primaryKey(),
			'wallet_id'   => $this->integer()->notNull(),
			'game_code'   => $this->string()->notNull(),
			'balance'     => $this->double(2)->defaultValue(0),
			'last_update' => $this->integer(),
			'verify_hash' => $this->string()->notNull(),
			'status'      => $this->integer()->defaultValue(Status::STATUS_ACTIVE),
		], $tableOptions);

		$this->addForeignKey('fk_wallet_sub_wallet', '{{%wallet_sub}}', 'wallet_id', '{{%wallet}}',
			'id');
	}

	/**
	 * @return bool|void|null
	 */
	public function down(){
		$this->dropTable('{{%wallet_sub}}');
	}
}
