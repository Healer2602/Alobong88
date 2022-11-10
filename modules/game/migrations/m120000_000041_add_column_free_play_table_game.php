<?php

namespace modules\game\migrations;

use yii\db\Migration;

/**
 * Class m120000_000041_add_column_free_play_table_game
 */
class m120000_000041_add_column_free_play_table_game  extends Migration{

	private $table = '{{%game}}';

	/**
	 * @return bool|void
	 */
	public function up(){
		$this->addColumn($this->table, 'free_to_play', $this->integer()->after('status'));
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropColumn($this->table, 'free_to_play');
	}
}