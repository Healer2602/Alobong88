<?php

namespace modules\block\migrations;

use yii\db\Migration;

/**
 * Class m200000_000030_add_column_image_mobile_table_block
 */
class m200000_000030_add_column_image_mobile_table_block extends Migration{

	/**
	 * {@inheritdoc}
	 */
	public function up(){
		$this->addColumn('{{%block}}', 'image_mobile', $this->text()->after('image'));
	}

	/**
	 * {@inheritdoc}
	 */
	public function down(){
		$this->dropColumn('{{%block}}', 'image_mobile');
	}
}
