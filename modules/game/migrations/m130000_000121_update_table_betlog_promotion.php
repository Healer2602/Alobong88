<?php

namespace modules\game\migrations;

use yii\db\Migration;

/**
 * Class m130000_000121_update_table_betlog_promotion
 */
class m130000_000121_update_table_betlog_promotion extends Migration{

	private $_table = '{{%game_betlog}}';

	/**
	 * @return void
	 */
	public function up(){
		$this->addColumn($this->_table, 'promotion_id', $this->integer()->after('total_rebate'));
	}
}