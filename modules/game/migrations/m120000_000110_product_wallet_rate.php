<?php

namespace modules\game\migrations;

use yii\db\Migration;

/**
 * Class m120000_000110_product_wallet_rate
 */
class m120000_000110_product_wallet_rate extends Migration{

	private $_table = '{{%product_wallet}}';

	/**
	 * @return bool|void
	 */
	public function up(){
		$this->addColumn($this->_table, 'rate',
			$this->float()->defaultValue(1)->after('vendor_id'));
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropColumn($this->_table, 'rate');
	}
}