<?php

namespace modules\promotion\migrations;

use yii\db\Migration;

/**
 * Class m150000_000010_create_table_map_promotion_wallet
 */
class m150000_000010_create_table_map_promotion_wallet extends Migration{

	/**
	 * @return bool|void
	 */
	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable('{{%promotion_wallet}}', [
			'promotion_id' => $this->integer()->notNull(),
			'product_code' => $this->string()->notNull()
		], $tableOptions);

		$this->createIndex('unq_promotion_id_product_code', '{{%promotion_wallet}}',
			['promotion_id', 'product_code'], TRUE);
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropTable('{{%promotion_wallet}}');
	}
}