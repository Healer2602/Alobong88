<?php

namespace modules\wallet\migrations;

use yii\db\Migration;

/**
 * Class m100000_100010_update_wallet_transaction_gateway
 */
class m100000_100010_update_wallet_transaction_gateway extends Migration{

	/**
	 * @return bool|void|null
	 */
	public function up(){
		$this->alterColumn('{{%wallet_transaction}}', 'gateway_id', $this->integer());

		$this->createIndex('idx_wallet_transaction_gateway', '{{%wallet_transaction}}',
			['gateway_id']);
	}

	/**
	 * @return bool|void|null
	 */
	public function down(){
		$this->alterColumn('{{%wallet_transaction}}', 'gateway_id', $this->string());
	}
}
