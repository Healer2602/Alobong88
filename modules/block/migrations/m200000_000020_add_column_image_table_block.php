<?php

namespace modules\block\migrations;

use yii\db\Migration;

/**
 * Class m200000_000020_add_column_image_table_block
 */
class m200000_000020_add_column_image_table_block extends Migration{

	/**
	 * {@inheritdoc}
	 */
	public function up(){
		$this->addColumn('{{%block}}', 'image',
			$this->db->schema->createColumnSchemaBuilder('LONGTEXT')->after('type'));
	}

	/**
	 * {@inheritdoc}
	 */
	public function down(){
		$this->dropColumn('{{%block}}', 'image');
	}
}
