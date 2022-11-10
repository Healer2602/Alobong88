<?php

namespace modules\wallet\migrations;

use yii\db\Migration;

/**
 * Class m100000_110011_wallet_sub_player
 */
class m100000_110011_wallet_sub_player extends Migration{

	private $_table = '{{%wallet_sub}}';

	/**
	 * @return bool|void|null
	 */
	public function up(){
		$this->addColumn($this->_table, 'player_id',
			$this->integer()->notNull()->after('wallet_id'));

		$this->execute("update {{%wallet_sub}} sub left join {{%wallet}} wallet on wallet.id = sub.wallet_id set sub.player_id = wallet.customer_id");

		$this->addForeignKey('fk_wallet_sub_player_id', $this->_table, 'player_id', '{{%customer}}',
			'id');
	}

	/**
	 * @return bool|void|null
	 */
	public function down(){
		$this->dropForeignKey('fk_wallet_sub_player_id', $this->_table);
		$this->dropColumn($this->_table, 'player_id');
	}
}
