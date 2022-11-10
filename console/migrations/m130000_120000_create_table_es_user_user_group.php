<?php

use yii\db\Migration;

class m130000_120000_create_table_es_user_user_group extends Migration{

	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable('{{%user_user_group}}', [
			'user_id'       => $this->integer()->notNull(),
			'user_group_id' => $this->integer()->notNull()
		], $tableOptions);

		$this->addPrimaryKey('PRIMARY_KEY', '{{%user_user_group}}', ['user_id', 'user_group_id']);
		$this->addForeignKey('fk_user_map_user_id', '{{%user_user_group}}', 'user_id', '{{%user}}',
			'id', 'NO ACTION', 'NO ACTION');
		$this->addForeignKey('fk_user_map_group_id', '{{%user_user_group}}', 'user_group_id',
			'{{%user_group}}', 'id', 'NO ACTION', 'NO ACTION');
	}

	public function down(){
		$this->dropTable('{{%user_user_group}}');
	}
}
