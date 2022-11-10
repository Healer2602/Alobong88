<?php

namespace modules\customer\migrations;

use yii\db\Migration;

/**
 * Class m200000_000010_customer_rank_default
 */
class m200000_000010_customer_rank_default extends Migration{

	/**
	 * @return bool|void
	 */
	public function up(){
		$this->addColumn('{{%customer_rank}}', 'is_default',
			$this->integer()->defaultValue(0)->after('status'));
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropColumn('{{%customer_rank}}', 'is_default');
	}
}
