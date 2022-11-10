<?php

use yii\db\Migration;

class m130000_100000_update_audit_trail extends Migration{

	private $_table_name = '{{%audit_trail}}';

	public function up(){
		$this->addColumn($this->_table_name, 'user_name', $this->string()->after('user_id'));
	}

	public function down(){
		$this->dropColumn('{{%audit_trail}}', 'user_name');
	}
}