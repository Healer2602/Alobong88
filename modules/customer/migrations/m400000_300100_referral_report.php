<?php

namespace modules\customer\migrations;

use yii\db\Migration;

class m400000_300100_referral_report extends Migration{

	private $_table_name = '{{%customer_referral_report}}';

	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable($this->_table_name, [
			'id'            => $this->primaryKey(),
			'referral_id'   => $this->integer()->notNull(),
			'date'          => $this->date()->notNull(),
			'active_users'  => $this->integer(),
			'total_invests' => $this->integer(),
			'profits'       => $this->double(),
			'commissions'   => $this->double(),
			'reported_at'   => $this->integer()
		], $tableOptions);

		$this->createIndex('idx_referral_report_date', $this->_table_name, 'date');
		$this->createIndex('unq_referral_report_referral_date', $this->_table_name,
			['referral_id', 'date'],
			TRUE);
	}

	public function down(){
		$this->dropTable($this->_table_name);
	}
}
