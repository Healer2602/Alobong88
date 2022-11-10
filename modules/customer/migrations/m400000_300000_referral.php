<?php

namespace modules\customer\migrations;

use common\base\Status;
use yii\db\Migration;

class m400000_300000_referral extends Migration{

	private $_table_name = '{{%customer_referral}}';

	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable($this->_table_name, [
			'id'           => $this->primaryKey(),
			'customer_id'  => $this->integer()->notNull(),
			'code'         => $this->string()->notNull()->unique(),
			'status'       => $this->integer()
			                       ->notNull()
			                       ->defaultValue(Status::STATUS_ACTIVE),
			'commission'   => $this->double(),
			'active_users' => $this->integer()->defaultValue(1),
			'created_by'   => $this->integer()->notNull(),
			'created_at'   => $this->integer()->notNull(),
			'updated_by'   => $this->integer(),
			'updated_at'   => $this->integer(),
		], $tableOptions);

		$this->createIndex('idx_referral_customer', $this->_table_name, 'customer_id');
	}

	public function down(){
		$this->dropTable($this->_table_name);
	}
}
