<?php

namespace modules\game\migrations;

use yii\db\Migration;

/**
 * Class m120000_000040_add_column_table_game
 */
class m120000_000040_add_column_table_game extends Migration{

	private $table = '{{%game}}';

	/**
	 * @return bool|void
	 */
	public function up(){
		$this->addColumn($this->table, 'ordering', $this->integer()->after('status'));
		$this->addColumn($this->table, 'feature', $this->tinyInteger(4)->after('status'));
		$this->addColumn($this->table, 'lines', $this->string()->after('icon'));
		$this->addColumn($this->table, 'min_bet', $this->float()->after('icon'));
		$this->addColumn($this->table, 'max_bet', $this->float()->after('icon'));
		$this->addColumn($this->table, 'rtp', $this->string()->after('icon'));
	}

	/**
	 * @return bool|void
	 */
	public function down(){
		$this->dropColumn($this->table, 'ordering');
		$this->dropColumn($this->table, 'feature');
		$this->dropColumn($this->table, 'lines');
		$this->dropColumn($this->table, 'min_bet');
		$this->dropColumn($this->table, 'max_bet');
		$this->dropColumn($this->table, 'rtp');
	}
}