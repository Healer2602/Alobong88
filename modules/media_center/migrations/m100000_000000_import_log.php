<?php

namespace modules\media_center\migrations;

use yii\db\Migration;

/**
 * Class m100000_000000_import_log
 */
class m100000_000000_import_log extends Migration{ //NOSONAR

	/**
	 * @return void
	 */
	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable('{{%import_log}}', [
			'id'           => $this->bigPrimaryKey(),
			'importer'     => $this->string(255)->notNull(),
			'validator'    => $this->string(500)->notNull(),
			'import_class' => $this->string(500)->notNull(),
			'status'       => $this->integer()->defaultValue(0),
			'filename'     => $this->text(),
			'description'  => $this->text(),
			'error_log'    => $this->text(),
			'created_at'   => $this->integer(),
			'created_by'   => $this->integer(),
			'updated_at'   => $this->integer(),
			'completed_at' => $this->integer(),
		], $tableOptions);
	}

	/**
	 * @return void
	 */
	public function down(){
		$this->dropTable('{{%import_log}}');
	}
}
