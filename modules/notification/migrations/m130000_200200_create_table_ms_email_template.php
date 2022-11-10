<?php

namespace modules\notification\migrations;

use yii\db\Migration;

/**
 * Class m130000_200200_create_table_ms_email_template
 */
class m130000_200200_create_table_ms_email_template extends Migration{

	/**
	 * @return bool|void
	 */
	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable('{{%email_template}}', [
			'id'          => $this->primaryKey(),
			'trigger_key' => $this->string(255),
			'name'        => $this->string(1000)->notNull(),
			'subject'     => $this->string(1000)->notNull(),
			'content'     => $this->text(),
			'status'      => $this->integer()->notNull()->defaultValue('10'),
			'language'    => $this->string(2),
			'created_by'  => $this->integer()->notNull(),
			'created_at'  => $this->integer()->notNull(),
			'updated_by'  => $this->integer(),
			'updated_at'  => $this->integer(),
		], $tableOptions);
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropTable('{{%email_template}}');
	}
}
