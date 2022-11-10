<?php

namespace modules\customer\migrations;

use yii\db\Migration;

class m400000_300010_referral_customer extends Migration{

	private $_table_name = '{{%customer}}';

	public function up(){
		$this->addColumn($this->_table_name, 'referral_id', $this->integer());
	}

	public function down(){
		$this->dropColumn($this->_table_name, 'referral_id');
	}
}
