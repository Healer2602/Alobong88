<?php

namespace backend\models;

use common\base\MessageSource;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Class Language
 *
 * @package backend\models
 */
class StringTranslate extends Model{

	public $translation;
	public $language;
	public $force = FALSE;

	/**
	 * @inheritDoc
	 */
	public function init(){
		parent::init();

		if ($this->force){
			$file_path = Yii::getAlias(MessageSource::CUSTOM_PATH) . "/default.yml";
		}else{
			$file_path = Yii::getAlias(MessageSource::CUSTOM_PATH) . "/{$this->language}.yml";
		}

		if (file_exists($file_path)){
			$this->translation = @file_get_contents($file_path);
		}
	}

	/**
	 * @return array
	 */
	public function rules(){
		return [
			['translation', 'required'],
			['translation', 'string'],
			['translation', 'validateTranslation']
		];
	}

	/**
	 * @param $attribute
	 */
	public function validateTranslation($attribute){
		if ($this->translation){
			try{
				$content = $this->translation;
				if (!empty($content)){
					$content  = str_replace(":", ": ", $content);
					$content  = str_replace(":  ", ": ", $content);
					$messages = Yaml::parse($content);
				}
			}catch (ParseException $exception){
				$messages = [];
			}

			if (!ArrayHelper::isAssociative($messages)){
				$this->addError($attribute,
					Yii::t('common', 'Translation file content is invalid.'));
			}
		}
	}

	/**
	 * @return bool
	 */
	public function upload(){
		if ($this->validate()){
			$content = $this->translation;
			$content = str_replace(":", ": ", $content);
			$content = str_replace(":  ", ": ", $content);

			$file_path = Yii::getAlias(MessageSource::CUSTOM_PATH) . "/{$this->language}.yml";

			return file_put_contents($file_path, $content);
		}

		return FALSE;
	}
}