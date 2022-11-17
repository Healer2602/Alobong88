<?php

namespace modules\website\migrations;

use yii\db\Migration;

class m120000_100000_create_table_menu extends Migration{

	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable('{{%menu}}', [
			'id'         => $this->primaryKey(),
			'name'       => $this->string()->notNull(),
			'menu_path'  => $this->string()->notNull(),
			'parent_id'  => $this->integer(),
			'lft'        => $this->integer()->notNull(),
			'rgt'        => $this->integer()->notNull(),
			'depth'      => $this->integer()->unsigned()->notNull(),
			'tree'       => $this->integer()->notNull(),
			'status'     => $this->integer()->defaultValue('10'),
			'icon'       => $this->text(),
			'params'     => $this->text(),
			'language'   => $this->string(2),
			'position'   => $this->string(),
			'created_at' => $this->integer(),
			'created_by' => $this->integer(),
			'updated_at' => $this->integer(),
			'updated_by' => $this->integer(),
		], $tableOptions);

		$this->createIndex('idx_menu_lft', '{{%menu}}', 'lft');
		$this->createIndex('idx_menu_language', '{{%menu}}', 'language');
		$this->createIndex('idx_menu_position', '{{%menu}}', 'position');
		$this->createIndex('idx_menu_rgt', '{{%menu}}', 'rgt');
		$this->addForeignKey('fk_system_parent_menu', '{{%menu}}', 'parent_id',
			'{{%menu}}', 'id');
	}

	public function down(){
		$this->dropTable('{{%menu}}');
	}
}
