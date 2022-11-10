<?php

use yii\db\Migration;

/**
 * Class m130000_500010_update_table_user_language
 */
class m130000_500010_update_table_user_language extends Migration{

	protected $table_name = '{{%user_language}}';

	/**
	 * @return void
	 */
	public function up(){
		$this->dropForeignKey('fk_user_language_user', $this->table_name);
		$this->createIndex('idx_user_language_user_id', $this->table_name, 'user_id');
	}
}