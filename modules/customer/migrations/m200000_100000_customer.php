<?php
namespace modules\customer\migrations;

use yii\db\Migration;

/**
 * Class m200000_100000_customer
 */
class m200000_100000_customer extends Migration{

	/**
	 * @return bool|void
	 */
	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable('{{%customer}}', [
			'id'                   => $this->primaryKey(),
			'customer_rank_id'     => $this->integer(),
			'name'                 => $this->string(),
			'username'             => $this->string()->notNull(),
			'email'                => $this->string()->notNull(),
			'phone_number'         => $this->string(),
			'dob'                  => $this->string(),
			'gender'               => $this->string(),
			'avatar'               => $this->string(1000),
			'auth_key'             => $this->string(32),
			'password_hash'        => $this->string(),
			'password_reset_token' => $this->string()->unique(),
			'status'               => $this->integer()->notNull()->defaultValue('10'),
			'created_by'           => $this->integer(),
			'created_at'           => $this->integer()->notNull(),
			'updated_by'           => $this->integer(),
			'updated_at'           => $this->integer(),
		], $tableOptions);

		$this->addForeignKey('fk_customer_rank', '{{%customer}}', 'customer_rank_id',
			'{{%customer_rank}}', 'id');
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropTable('{{%customer}}');
	}
}
