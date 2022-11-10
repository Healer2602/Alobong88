<?php

namespace modules\website\models;

use common\models\SettingForm;
use Yii;


/**
 * Class HtaccessSetting
 *
 * @package modules\website\models
 */
class HtaccessSetting extends SettingForm{

	public $content;
	public $htaccess;

	/**
	 * @return array
	 */
	public function rules(){
		return [
			['htaccess', 'string'],
			['content', 'safe'],
			['content', 'required'],
		];
	}

	/**
	 * @return array
	 */
	public function attributeLabels(){
		return [
			'content' => Yii::t('common', 'File Content'),
		];
	}

	/**
	 * @return bool|string
	 */
	private function _findFilePath(){
		$file = Yii::getAlias('@public/web/.htaccess');
		if (!is_file($file)){
			@file_put_contents($file, '');
		}

		return $file;
	}

	/**
	 * @return string
	 */
	private function _findDefaultContent(){
		return <<< HTML
# BEGIN HTACCESS
RewriteEngine on
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . index.php
# END HTACCESS
HTML;
	}

	/**
	 * @inheritDoc
	 */
	public function getValues(){
		parent::getValues();

		$this->content = $this->htaccess;
	}

	/**
	 * @return int|void
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\db\Exception
	 */
	public function save(){
		$file    = $this->_findFilePath();
		$content = $this->content . "\n" . $this->_findDefaultContent();

		$this->htaccess = $this->content;
		$this->content  = [$content];
		if (parent::save()){
			return @file_put_contents($file, $content);
		}

		$this->content = $this->htaccess;

		return FALSE;
	}
}