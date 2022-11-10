<?php

namespace modules\customer\migrations;

use yii\db\Migration;

/**
 * Class m200000_100021_add_column_currency_table_customer
 */
class m200000_100021_add_column_currency_table_customer extends Migration{

	const TABLE_NAME = '{{%customer}}';

	/**
	 * @return bool|void
	 */
	public function up(){
		$this->addColumn(self::TABLE_NAME, 'currency',
			$this->string(10)->after('country_code'));
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropColumn(self::TABLE_NAME, 'currency');
	}
}
