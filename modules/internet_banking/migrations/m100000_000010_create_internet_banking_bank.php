<?php

namespace modules\internet_banking\migrations;

use yii\db\Migration;

/**
 * Class m100000_000010_create_internet_banking_bank
 */
class m100000_000010_create_internet_banking_bank extends Migration{

	/**
	 * @return bool|void
	 */
	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}
		$this->createTable('{{%internet_banking_bank}}', [
			'id'            => $this->primaryKey(),
			'name'          => $this->string(255),
			'currency_code' => $this->string(3),
			'code'          => $this->string(20)->unique(),
			'logo'          => $this->string(2000),
			'content'       => $this->text(),
			'status'        => $this->integer()->defaultValue('10'),
		], $tableOptions);
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropTable('{{%internet_banking_bank}}');
	}
}
