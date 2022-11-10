<?php

namespace modules\wallet\migrations;

use yii\db\Migration;

/**
 * Class m100000_300000_add_column_icon_n_option_table_wallet_gateway
 */
class m100000_300000_add_column_icon_n_option_table_wallet_gateway extends Migration{

	/**
	 * @return bool|void|null
	 */
	public function up(){
		$this->addColumn('{{%wallet_gateway}}', 'icon', $this->text()->after('fee'));
		$this->addColumn('{{%wallet_gateway}}', 'option', $this->string()->after('fee'));
	}

	/**
	 * @return bool|void|null
	 */
	public function down(){
		$this->dropColumn('{{%wallet_gateway}}', 'icon');
		$this->dropColumn('{{%wallet_gateway}}', 'option');
	}
}