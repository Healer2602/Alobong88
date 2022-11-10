<?php

use yii\db\Migration;

/**
 * Class m130000_300020_add_table_language_translation
 */
class m130000_300020_add_table_language_translation extends Migration{

	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable('{{%language_translation}}', [
			'id'          => $this->primaryKey(),
			'object_id'   => $this->string()->notNull(),
			'item_id'     => $this->integer()->notNull(),
			'language'    => $this->string(2)->notNull(),
			'language_id' => $this->string()->notNull(),
		], $tableOptions);
	}

	public function down(){
		$this->dropTable('{{%language_translation}}');
	}
}
