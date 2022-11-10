<?php

namespace modules\wallet\migrations;

use yii\db\Migration;

/**
 * Class m100000_100010_update_wallet_auto_transfer
 */
class m100000_100011_update_wallet_total_deposit extends Migration{

	/**
	 * @return bool|void|null
	 */
	public function up(){
		$this->addColumn('{{%wallet}}', 'total_deposit',
			$this->double(2)->unsigned()->defaultValue(0));
	}

	/**
	 * @return bool|void|null
	 */
	public function down(){
		$this->dropColumn('{{%wallet}}', 'total_deposit');
	}
}
