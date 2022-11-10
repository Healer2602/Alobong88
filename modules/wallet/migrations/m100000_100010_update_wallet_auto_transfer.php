<?php

namespace modules\wallet\migrations;

use yii\db\Migration;

/**
 * Class m100000_100010_update_wallet_auto_transfer
 */
class m100000_100010_update_wallet_auto_transfer extends Migration{

	/**
	 * @return bool|void|null
	 */
	public function up(){
		$this->addColumn('{{%wallet}}', 'auto_transfer', $this->boolean()->defaultValue(0));
	}

	/**
	 * @return bool|void|null
	 */
	public function down(){
		$this->dropColumn('{{%wallet}}', 'auto_transfer');
	}
}
