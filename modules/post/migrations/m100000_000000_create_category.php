<?php

namespace modules\post\migrations;

use yii\db\Migration;

class m100000_000000_create_category extends Migration{

	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable('{{%post_category}}', [
			'id'          => $this->primaryKey(),
			'name'        => $this->string(255)->notNull(),
			'slug'        => $this->string(255)->notNull()->unique(),
			'description' => $this->text(),
			'status'      => $this->integer()->notNull()->defaultValue('10'),
			'type'        => $this->string(10)->notNull()->defaultValue('post'),
			'language'    => $this->string(2),
			'created_by'  => $this->integer()->notNull(),
			'created_at'  => $this->integer()->notNull(),
			'updated_by'  => $this->integer(),
			'updated_at'  => $this->integer(),
		], $tableOptions);

		$this->insert('{{%post_category}}', [
			'id'         => 1,
			'name'       => 'Uncategorized',
			'slug'       => 'uncategorized',
			'created_by' => 1,
			'created_at' => time()
		]);

		$this->createIndex('idx_post_category_type', '{{%post_category}}', 'type');
	}

	public function down(){
		$this->dropTable('{{%post_category}}');
	}
}
