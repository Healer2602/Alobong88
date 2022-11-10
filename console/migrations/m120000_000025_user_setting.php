<?php

use yii\db\Migration;

/**
 * Class m120000_000020_user_setting
 */
class m120000_000025_user_setting extends Migration{

	public function up(){
		$this->dropTable('{{%user_setting}}');
	}

	public function down(){

		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable('{{%user_setting}}', [
			'id'            => $this->primaryKey(),
			'user_id'       => $this->integer()->notNull(),
			'collapse_menu' => $this->tinyInteger()->defaultValue(0),
			'darkmode'      => $this->tinyInteger()->defaultValue(0),
			'created_at'    => $this->integer()->notNull(),
			'updated_at'    => $this->integer()->notNull(),
		], $tableOptions);

		$this->addForeignKey('fk_user_setting_user', '{{%user_setting}}', 'user_id',
			'{{%user}}', 'id');
	}
}
