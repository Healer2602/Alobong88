<?php

namespace modules\promotion\migrations;

use yii\db\Migration;

/**
 * Class m160000_000000_create_table_promotion_join
 */
class m160000_000000_create_table_promotion_join extends Migration{

	private $_table = '{{%promotion_joining}}';

	/**
	 * @return void
	 */
	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable($this->_table, [
			'id'             => $this->primaryKey(),
			'player_id'      => $this->string()->notNull(),
			'wallet_id'      => $this->integer(),
			'promotion_id'   => $this->integer(),
			'promotion_type' => $this->string()->notNull(),
			'joined_at'      => $this->integer()->notNull(),
			'expired_at'     => $this->integer()->notNull(),
			'reset'          => $this->boolean()->defaultValue(FALSE),
			'params'         => $this->text()
		], $tableOptions);
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropTable($this->_table);
	}
}