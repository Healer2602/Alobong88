<?php

use yii\db\Migration;

class m130000_100000_audit_trail extends Migration{

	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable('{{%audit_trail}}', [
			'id'         => $this->primaryKey(),
			'system'     => $this->string()->notNull(),
			'module'     => $this->string(),
			'action'     => $this->string()->notNull(),
			'message'    => $this->text(),
			'user_id'    => $this->integer(),
			'ip_address' => $this->string(255),
			'status'     => $this->string(),
			'created_at' => $this->integer(),

		], $tableOptions);

		$this->createIndex('idx_audit_trail_user_id', '{{%audit_trail}}', 'user_id');
		$this->createIndex('idx_audit_trail_system', '{{%audit_trail}}', 'system');
		$this->createIndex('idx_audit_trail_module', '{{%audit_trail}}', 'module');
	}


	public function down(){
		$this->dropTable('{{%audit_trail}}');
	}
}