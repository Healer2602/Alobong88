<?php

use yii\db\Migration;

class m130000_110000_create_table_es_user_group_permission extends Migration{

	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable('{{%user_group_permission}}', [
			'user_group_id'      => $this->integer()->notNull(),
			'user_permission_id' => $this->integer()->notNull(),
		], $tableOptions);

		$this->addForeignKey('fk_group_permission', '{{%user_group_permission}}',
			'user_permission_id',
			'{{%user_permission}}', 'id', 'NO ACTION', 'NO ACTION');
		$this->addForeignKey('fk_user_group', '{{%user_group_permission}}', 'user_group_id',
			'{{%user_group}}', 'id', 'NO ACTION', 'NO ACTION');
	}

	public function down(){
		$this->dropTable('{{%user_group_permission}}');
	}
}
