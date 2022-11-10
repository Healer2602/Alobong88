<?php

use yii\db\Migration;

/**
 * Class m120000_000015_update_user
 */
class m120000_000015_update_user extends Migration{

	protected $table_name = '{{%user}}';

	/**
	 * @return bool|void|null
	 */
	public function up(){
		$this->dropColumn($this->table_name, 'avatar');
		$this->dropColumn($this->table_name, 'phone_number');
	}

	/**
	 * @return bool|void|null
	 */
	public function down(){
		$this->addColumn($this->table_name, 'avatar', $this->string(1000));
		$this->addColumn($this->table_name, 'phone_number', $this->string(20));
	}
}
