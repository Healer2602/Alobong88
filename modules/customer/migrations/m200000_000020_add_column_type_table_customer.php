<?php

namespace modules\customer\migrations;

use yii\db\Migration;

/**
 * Class m200000_000020_add_column_type_table_customer
 */
class m200000_000020_add_column_type_table_customer extends Migration{

	/**
	 * @return bool|void
	 */
	public function up(){
		$this->addColumn('{{%customer_rank}}', 'type',
			$this->string()->after('is_default'));
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropColumn('{{%customer_rank}}', 'type');
	}
}
