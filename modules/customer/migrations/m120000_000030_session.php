<?php

namespace modules\customer\migrations;

use yii\db\Migration;

/**
 * Class m120000_000030_session
 */
class m120000_000030_session extends Migration{

	/**
	 * @return bool|void
	 */
	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable('{{%session}}', [
			'id'          => $this->string(40)->notNull(),
			'expire'      => $this->integer(),
			'data'        => 'BLOB',
			'customer_id' => $this->integer(),
		], $tableOptions);

		$this->addPrimaryKey('pk_session', '{{%session}}', 'id');
		$this->createIndex('idx_session_customer', '{{%session}}', 'customer_id');
		$this->createIndex('idx_session_expire', '{{%session}}', 'expire');
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropTable('{{%session}}');
	}
}
