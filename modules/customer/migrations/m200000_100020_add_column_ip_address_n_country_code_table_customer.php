<?php
namespace modules\customer\migrations;

use yii\db\Migration;

/**
 * Class m200000_100020_add_column_ip_address_n_country_code_table_customer
 */
class m200000_100020_add_column_ip_address_n_country_code_table_customer extends Migration{

	const TABLE_NAME = '{{%customer}}';

	/**
	 * @return bool|void
	 */
	public function up(){
		$this->addColumn(self::TABLE_NAME, 'country_code',
			$this->string(2)->after('avatar'));
		$this->addColumn(self::TABLE_NAME, 'ip_address',
			$this->string()->after('avatar'));
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropColumn(self::TABLE_NAME, 'country_code');
		$this->dropColumn(self::TABLE_NAME, 'ip_address');
	}
}
