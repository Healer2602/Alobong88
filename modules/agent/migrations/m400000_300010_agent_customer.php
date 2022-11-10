<?php

namespace modules\agent\migrations;

use yii\db\Migration;

class m400000_300010_agent_customer extends Migration{

	private $_table_name = '{{%customer}}';

	public function up(){
		$this->addColumn($this->_table_name, 'agent_id', $this->integer());
	}

	public function down(){
		$this->dropColumn($this->_table_name, 'agent_id');
	}
}
