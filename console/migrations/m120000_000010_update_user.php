<?php

use yii\db\Migration;

/**
 * Class m180812_052110_update_user
 */
class m120000_000010_update_user extends Migration{

	public function up(){
		$this->addColumn('{{%user}}', 'name', $this->string(1000));
		$this->addColumn('{{%user}}', 'avatar', $this->string(1000));
		$this->addColumn('{{%user}}', 'phone_number', $this->string(20));
	}

	public function down(){
		$this->dropColumn('{{%user}}', 'name');
		$this->dropColumn('{{%user}}', 'avatar');
		$this->dropColumn('{{%user}}', 'phone_number');
	}
}
