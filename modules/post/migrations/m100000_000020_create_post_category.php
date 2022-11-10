<?php

namespace modules\post\migrations;

use yii\db\Migration;

class m100000_000020_create_post_category extends Migration{

	public function up(){
		$tableOptions = NULL;
		if ($this->db->driverName === 'mysql'){
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}

		$this->createTable('{{%post_category_map}}', [
			'post_id'          => $this->integer()->notNull(),
			'post_category_id' => $this->integer()->notNull(),
		], $tableOptions);

		$this->addPrimaryKey('pk_post_category_map', '{{%post_category_map}}',
			['post_id', 'post_category_id']);

		$this->addForeignKey('fk_post_category_post', '{{%post_category_map}}', 'post_id',
			'{{%post}}', 'id');

		$this->addForeignKey('fk_post_category_category', '{{%post_category_map}}',
			'post_category_id',
			'{{%post_category}}', 'id');
	}

	public function down(){
		$this->dropTable('{{%post_category_map}}');
	}
}
