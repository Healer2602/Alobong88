<?php

namespace modules\customer\migrations;

use yii\db\Migration;

/**
 * Class m200000_100022_add_column_account_table_customer
 */
class m200000_100022_add_column_account_table_customer extends Migration{

	const TABLE_NAME = '{{%customer}}';

	/**
	 * @return bool|void
	 */
	public function up(){
		$this->addColumn(self::TABLE_NAME, 'has_account',
			$this->boolean()->after('currency')->defaultValue(FALSE));
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropColumn(self::TABLE_NAME, 'has_account');
	}
}
