<?php
namespace modules\customer\migrations;

use yii\db\Migration;

/**
 * Class m200000_100010_customer_auth
 */
class m200000_100010_customer_auth extends Migration{

	/**
	 * @return bool|void
	 */
	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable('{{%customer_auth}}', [
			'id'          => $this->primaryKey(),
			'customer_id' => $this->integer()->notNull(),
			'source'      => $this->string()->notNull(),
			'source_id'   => $this->string()->notNull(),
		], $tableOptions);

		$this->addForeignKey('fk_customer_auth_customer', '{{%customer_auth}}', 'customer_id',
			'{{%customer}}', 'id');
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropTable('{{%customer_auth}}');
	}
}
