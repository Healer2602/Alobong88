<?php

namespace modules\customer\migrations;

use yii\db\Migration;

/**
 * Class m200000_000011_add_columns_table_customer_rank
 */
class m200000_000011_add_columns_table_customer_rank extends Migration{

	/**
	 * @return bool|void
	 */
	public function up(){
		$this->addColumn('{{%customer_rank}}', 'daily_limit_balance',
			$this->integer()->after('description'));
		$this->addColumn('{{%customer_rank}}', 'daily_count_balance',
			$this->integer()->after('description'));
		$this->addColumn('{{%customer_rank}}', 'withdraw_limit_balance',
			$this->integer()->after('description'));
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropColumn('{{%customer_rank}}', 'daily_limit_balance');
		$this->dropColumn('{{%customer_rank}}', 'daily_count_balance');
		$this->dropColumn('{{%customer_rank}}', 'withdraw_limit_balance');
	}
}
