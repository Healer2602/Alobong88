<?php

namespace modules\game\migrations;

use yii\db\Migration;

/**
 * Class m160000_000010_betlog_provider_key
 */
class m160000_000010_betlog_provider_key extends Migration{

	private $_table = '{{%betlog_provider}}';

	/**
	 * @return bool|void
	 */
	public function up(){
		$this->createIndex('unq_betlog_provider_type_vendor_code', $this->_table, ['code'], TRUE);
		$this->createIndex('idx_betlog_provider_type_vendor', $this->_table, ['vendor_id']);
	}
}