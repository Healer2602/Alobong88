<?php

namespace modules\ezp\migrations;

use yii\db\Migration;

/**
 * Class m100000_000010_create_bank
 */
class m100000_000010_create_bank extends Migration{

	/**
	 * @return bool|void
	 */
	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}
		$this->createTable('{{%ezp_bank}}', [
			'id'            => $this->primaryKey(),
			'name'          => $this->string(255),
			'logo'          => $this->text(),
			'currency_code' => $this->string(3),
			'code'          => $this->string(20)->unique(),
			'maximum'       => $this->float()->defaultValue(0),
			'status'        => $this->integer()->defaultValue('10'),
			'visibility'    => $this->integer()->defaultValue(0)
		], $tableOptions);

		$this->createIndex('idx_bank_currency_code_currency', '{{%ezp_bank}}',
			'currency_code');
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropTable('{{%ezp_bank}}');
	}
}
