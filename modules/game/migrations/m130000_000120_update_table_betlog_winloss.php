<?php

namespace modules\game\migrations;

use yii\db\Migration;

/**
 * Class m130000_000120_update_table_betlog_winloss
 */
class m130000_000120_update_table_betlog_winloss extends Migration{

	private $_table = '{{%game_betlog}}';

	/**
	 * @return void
	 */
	public function up(){
		$this->addColumn($this->_table, 'winloss', $this->float()->after('amount'));
	}
}