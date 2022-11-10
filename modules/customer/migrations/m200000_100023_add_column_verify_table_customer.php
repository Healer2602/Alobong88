<?php

namespace modules\customer\migrations;

use yii\db\Migration;

/**
 * Class m200000_100023_add_column_verify_table_customer
 */
class m200000_100023_add_column_verify_table_customer extends Migration{

	const TABLE_NAME = '{{%customer}}';

	/**
	 * @return bool|void
	 */
	public function up(){
		$this->addColumn(self::TABLE_NAME, 'verify',
			$this->text()->after('has_account'));
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropColumn(self::TABLE_NAME, 'verify');
	}
}
