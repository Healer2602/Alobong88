<?php

namespace modules\promotion\migrations;

use yii\db\Migration;

/**
 * Class m150000_000000_create_table_promotion
 */
class m150000_000000_create_table_promotion extends Migration{

	/**
	 * @return bool|void
	 */
	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable('{{%promotion}}', [
			'id'                => $this->primaryKey(),
			'name'              => $this->string(),
			'type'              => $this->string(),
			'start_date'        => $this->integer(),
			'end_date'          => $this->integer(),
			'min_round'         => $this->integer(),
			'bonus_rate'        => $this->double(),
			'min_deposit'       => $this->double(),
			'max_bonus'         => $this->double(),
			'min_bonus'         => $this->double(),
			'refund_amount'     => $this->double(),
			'maximum_promotion' => $this->integer(),
			'number_promotion'  => $this->integer(),
			'excluding_revenue' => $this->string(),
			'exclude_promotion' => $this->string(),
			'status'            => $this->tinyInteger(4),
			'created_at'        => $this->integer(),
			'created_by'        => $this->integer(),
			'updated_at'        => $this->integer(),
			'updated_by'        => $this->integer(),
		], $tableOptions);
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropTable('{{%promotion}}');
	}
}