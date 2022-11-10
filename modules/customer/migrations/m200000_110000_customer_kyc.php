<?php

namespace modules\customer\migrations;

use yii\db\Migration;

/**
 * Class m200000_110000_customer_kyc
 */
class m200000_110000_customer_kyc extends Migration{

	/**
	 * @return bool|void
	 */
	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable('{{%customer_kyc}}', [
			'id'          => $this->primaryKey(),
			'customer_id' => $this->integer()->notNull(),
			'front_image' => $this->text(),
			'back_image'  => $this->text(),
			'reason'      => $this->string(),
			'status'      => $this->integer()->notNull()->defaultValue('0'),
			'created_by'  => $this->integer(),
			'created_at'  => $this->integer()->notNull(),
			'updated_by'  => $this->integer(),
			'updated_at'  => $this->integer(),
		], $tableOptions);

		$this->addForeignKey('fk_customer_kyc_customer', '{{%customer_kyc}}', 'customer_id',
			'{{%customer}}', 'id');
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropTable('{{%customer_kyc}}');
	}
}
