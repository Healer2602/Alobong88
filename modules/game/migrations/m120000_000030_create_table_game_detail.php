<?php

namespace modules\game\migrations;

use yii\db\Migration;

/**
 * Class m120000_000030_create_table_game_detail
 */
class m120000_000030_create_table_game_detail extends Migration{

	/**
	 * @return bool|void
	 */
	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable('{{%game_detail}}', [
			'id'       => $this->primaryKey(),
			'game_id'  => $this->integer()->notNull(),
			'name'     => $this->string(),
			'language' => $this->string(2),
			'icon'     => $this->text(),
		], $tableOptions);

		$this->addForeignKey('fk_game_detail_game_key', '{{%game_detail}}', 'game_id', '{{%game}}',
			'id');
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropTable('{{%game_detail}}');
	}
}