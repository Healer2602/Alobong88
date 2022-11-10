<?php

use yii\db\Migration;

class m130000_300000_create_table_setting extends Migration{

	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable('{{%setting}}', [
			'key'   => $this->string(255)->unique()->notNull(),
			'value' => $this->text()
		], $tableOptions);

		$this->addPrimaryKey('pk_setting_key', '{{%setting}}', 'key');
	}

	public function down(){
		$this->dropTable('{{%setting}}');
	}
}
