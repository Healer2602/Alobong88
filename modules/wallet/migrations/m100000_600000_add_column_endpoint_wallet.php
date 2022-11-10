<?php

namespace modules\wallet\migrations;

use yii\db\Migration;

/**
 * Class m100000_600000_add_column_endpoint_wallet
 */
class m100000_600000_add_column_endpoint_wallet extends Migration{

	/**
	 * @return bool|void|null
	 */
	public function up(){
		$this->addColumn('{{%wallet_gateway}}', 'endpoint', $this->string()->after('fee'));
	}

	/**
	 * @return bool|void|null
	 */
	public function down(){
		$this->dropColumn('{{%wallet_gateway}}', 'endpoint');
	}
}