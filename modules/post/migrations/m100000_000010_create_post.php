<?php

namespace modules\post\migrations;

use yii\db\Migration;

class m100000_000010_create_post extends Migration{

	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable('{{%post}}', [
			'id'           => $this->primaryKey(),
			'name'         => $this->string(255)->notNull(),
			'slug'         => $this->string(255)->notNull()->unique(),
			'thumbnail'    => $this->string(1000),
			'intro'        => $this->text(),
			'content'      => 'LONGTEXT',
			'tags'         => $this->text(),
			'related_tags' => $this->text(),
			'category_id'  => $this->integer()->defaultValue(NULL),
			'status'       => $this->integer()->notNull()->defaultValue('10'),
			'ordering'     => $this->integer()->notNull()->defaultValue('1'),
			'language'     => $this->string(2),
			'type'         => $this->string(100)->notNull()->defaultValue('post'),
			'position'     => $this->string(255),
			'created_by'   => $this->integer()->notNull(),
			'created_at'   => $this->integer()->notNull(),
			'updated_by'   => $this->integer(),
			'updated_at'   => $this->integer(),
		], $tableOptions);

		$this->addForeignKey('fk_post_category', '{{%post}}', 'category_id',
			'{{%post_category}}', 'id');

		$this->createIndex('idx_unq_post', '{{%post}}', ['slug', 'type'], TRUE);
	}

	public function down(){
		$this->dropTable('{{%post}}');
	}
}
