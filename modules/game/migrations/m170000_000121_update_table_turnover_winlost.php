<?php

namespace modules\game\migrations;

use yii\db\Migration;

/**
 * Class m170000_000121_update_table_turnover_winlost
 */
class m170000_000121_update_table_turnover_winlost extends Migration{

	private $_table = '{{%game_turnover}}';

	/**
	 * @return void
	 */
	public function up(){
		$this->addColumn($this->_table, 'winloss', $this->float()->after('turnover'));
	}
}