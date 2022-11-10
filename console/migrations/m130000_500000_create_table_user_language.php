<?php

use yii\db\Migration;

/**
 * Class m130000_500000_create_table_user_language
 */
class m130000_500000_create_table_user_language extends Migration{

	protected $table_name = '{{%user_language}}';

	/**
	 * @return void
	 */
	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable($this->table_name, [
			'user_id'  => $this->integer()->unique(),
			'language' => $this->string(2)->notNull(),
		], $tableOptions);

		$this->addForeignKey('fk_user_language_user', $this->table_name, 'user_id',
			'{{%user}}', 'id');
	}

	/**
	 * @return void
	 */
	public function down(){
		$this->dropTable($this->table_name);
	}
}