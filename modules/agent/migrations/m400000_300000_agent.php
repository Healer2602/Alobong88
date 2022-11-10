<?php

namespace modules\agent\migrations;

use common\base\Status;
use yii\db\Migration;

class m400000_300000_agent extends Migration{

	private $_table_name = '{{%agent}}';

	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable($this->_table_name, [
			'id'     => $this->primaryKey(),
			'name'   => $this->string(),
			'email'  => $this->string()->unique(),
			'code'   => $this->string()->notNull()->unique(),
			'status' => $this->integer()
			                 ->notNull()
			                 ->defaultValue(Status::STATUS_INACTIVE),

			'active'  => $this->integer(),
			'range_1' => $this->float(),
			'range_2' => $this->float(),
			'range_3' => $this->float(),

			'created_at' => $this->integer(),
			'updated_by' => $this->integer(),
			'updated_at' => $this->integer(),
		], $tableOptions);
	}

	public function down(){
		$this->dropTable($this->_table_name);
	}
}
