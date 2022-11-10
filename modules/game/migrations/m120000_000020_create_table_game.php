<?php

namespace modules\game\migrations;

use yii\db\Migration;

/**
 * Class m120000_000020_create_table_game
 */
class m120000_000020_create_table_game extends Migration{

	/**
	 * @return bool|void
	 */
	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable('{{%game}}', [
			'id'         => $this->primaryKey(),
			'vendor_id'  => $this->integer()->notNull(),
			'type_id'    => $this->integer()->notNull(),
			'name'       => $this->string()->notNull(),
			'code'       => $this->string()->notNull(),
			'icon'       => $this->text(),
			'status'     => $this->tinyInteger(4),
			'created_at' => $this->integer(),
			'created_by' => $this->integer(),
			'updated_at' => $this->integer(),
			'updated_by' => $this->integer(),
		], $tableOptions);

		$this->addForeignKey('fk_game_vendor_key', '{{%game}}', 'vendor_id', '{{%vendor}}', 'id');
		$this->addForeignKey('fk_game_game_type_key', '{{%game}}', 'type_id', '{{%game_type}}', 'id');
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropTable('{{%game}}');
	}
}