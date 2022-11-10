<?php

use yii\db\Migration;

/**
 * Class m130000_300010_add_table_language
 */
class m130000_300010_add_table_language extends Migration{

	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable('{{%language}}', [
			'id'         => $this->primaryKey(),
			'name'       => $this->string(255)->notNull(),
			'key'        => $this->string()->notNull()->unique(),
			'is_default' => $this->tinyInteger(2),
			'status'     => $this->integer()->notNull()->defaultValue('10'),
			'created_by' => $this->integer()->notNull(),
			'created_at' => $this->integer()->notNull(),
			'updated_by' => $this->integer(),
			'updated_at' => $this->integer(),
		], $tableOptions);

		$this->insert('{{%language}}', [
			'id'         => 1,
			'name'       => 'English',
			'key'        => 'en',
			'is_default' => 1,
			'status'     => 10,
			'created_by' => 1,
			'created_at' => time(),
			'updated_by' => NULL,
			'updated_at' => NULL
		]);
	}

	public function down(){
		$this->dropTable('{{%language}}');
	}
}
