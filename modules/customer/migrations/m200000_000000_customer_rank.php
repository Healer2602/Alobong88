<?php

namespace modules\customer\migrations;

use yii\db\Migration;

/**
 * Class m200000_000000_customer_rank
 */
class m200000_000000_customer_rank extends Migration{

	/**
	 * @return bool|void
	 */
	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable('{{%customer_rank}}', [
			'id'          => $this->primaryKey(),
			'name'        => $this->string(),
			'description' => $this->string(1000)->notNull(),
			'status'      => $this->integer()->notNull()->defaultValue('10'),
			'created_by'  => $this->integer()->notNull(),
			'created_at'  => $this->integer()->notNull(),
			'updated_by'  => $this->integer(),
			'updated_at'  => $this->integer(),
		], $tableOptions);
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropTable('{{%customer_rank}}');
	}
}
