<?php

namespace common\base;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use Yii;
use yii\helpers\ArrayHelper;
use yii\i18n\PhpMessageSource;

/**
 * Class MessageSource
 *
 * @package common\base
 */
class MessageSource extends PhpMessageSource{

	const CUSTOM_PATH = '@common/messages/custom';

	/**
	 * @param string $category
	 * @param string $language
	 *
	 * @return array|void
	 */
	protected function loadMessages($category, $language){
		$messages = parent::loadMessages($category, $language);
		if (Yii::$app->id == 'admin'){
			return $messages;
		}

		$custom_messages = $this->loadCustomMessage($language);

		return ArrayHelper::merge($messages, $custom_messages);
	}

	/**
	 * @param $language
	 *
	 * @return array|mixed
	 */
	private function loadCustomMessage($language){
		if ($language == 'zh-CN'){
			$custom_language = 'cn';
		}elseif ($language == 'zh-TW'){
			$custom_language = 'tw';
		}else{
			$custom_language = $language;
		}

		$language_path = Yii::getAlias(self::CUSTOM_PATH) . "/{$custom_language}.yml";

		try{
			$messages = Yaml::parseFile($language_path);
		}catch (ParseException $exception){
			$messages = [];
		}

		return $messages;
	}
}