<?php

namespace modules\agent\migrations;

use yii\db\Migration;

class m400000_300020_agent_settings extends Migration{

	private $_table_name = '{{%agent}}';

	public function up(){
		$this->addColumn($this->_table_name, 'deposit_rate', $this->float()->after('range_3'));
		$this->addColumn($this->_table_name, 'withdrawal_rate',
			$this->float()->after('deposit_rate'));
		$this->addColumn($this->_table_name, 'administration_rate',
			$this->float()->after('withdrawal_rate'));
	}

	public function down(){
		$this->dropColumn($this->_table_name, 'deposit_rate');
		$this->dropColumn($this->_table_name, 'withdrawal_rate');
		$this->dropColumn($this->_table_name, 'administration_rate');
	}
}
