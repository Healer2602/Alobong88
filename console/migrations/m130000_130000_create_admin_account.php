<?php

use yii\db\Migration;

class m130000_130000_create_admin_account extends Migration{

	public function up(){
		$this->insert('{{%user}}', [
			'username'      => 'root',
			'email'         => 'root@example.com',
			'auth_key'      => Yii::$app->security->generateRandomString(),
			'password_hash' => Yii::$app->security->generatePasswordHash('root'),
			'updated_at'    => time(),
			'created_at'    => time()
		]);

		$this->insert('{{%user_group}}', [
			'name'       => 'Administrator',
			'is_primary' => 1,
			'created_by' => 1,
			'created_at' => time()
		]);

		$this->insert('{{%user_user_group}}', [
			'user_id'       => 1,
			'user_group_id' => 1
		]);

	}

	public function down(){
		return FALSE;
	}
}
