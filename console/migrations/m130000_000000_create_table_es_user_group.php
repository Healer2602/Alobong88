<?php

use yii\db\Migration;

class m130000_000000_create_table_es_user_group extends Migration{

	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable('{{%user_group}}', [
			'id'         => $this->primaryKey(),
			'name'       => $this->string()->notNull(),
			'status'     => $this->integer()->notNull()->defaultValue('10'),
			'is_primary' => $this->tinyInteger()->notNull()->defaultValue('0'),
			'created_by' => $this->integer()->notNull(),
			'created_at' => $this->integer()->notNull(),
			'updated_by' => $this->integer(),
			'updated_at' => $this->integer(),
		], $tableOptions);
	}

	public function down(){
		$this->dropTable('{{%user_group}}');
	}
}
