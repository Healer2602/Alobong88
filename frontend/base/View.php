<?php

namespace frontend\base;

use modules\website\models\WebsiteSetting;
use Yii;
use yii\base\Event;
use yii\web\Response;

/**
 * Class View
 *
 * @package frontend\base
 *
 * @inheritDoc
 *
 * @property WebsiteSetting $setting
 */
class View extends \yii\web\View{

	/**
	 * @var \yii\web\AssetBundle
	 */
	public $asset;

	/**
	 * Enable or disable compression, by default compression is enabled.
	 *
	 * @var bool
	 */
	public $compress = YII_ENV_PROD;

	/**
	 * @param $message
	 * @param array $params
	 *
	 * @return string
	 */
	public function t($message, $params = []){
		$translation = Yii::$app->getI18n()->translations;
		$category    = 'common';

		if (!empty($this->context->module->id) && !empty($translation[$this->context->module->id])){
			$category = $this->context->module->id;
		}

		return Yii::t($category, $message, $params);
	}

	/**
	 * @var  \modules\website\models\WebsiteSetting
	 */
	private $_setting = NULL;

	/**
	 * @return \modules\website\models\WebsiteSetting
	 */
	public function getSetting(){
		if ($this->_setting === NULL){
			$this->_setting = new WebsiteSetting();
			$this->_setting->getValues();
		}

		return $this->_setting;
	}

	/**
	 * @inheritdoc
	 */
	public function init(){
		parent::init();
		if ($this->compress === TRUE){
			Yii::$app->response->on(Response::EVENT_BEFORE_SEND,
				function (Event $event){
					$response = $event->sender;
					if ($response->format === Response::FORMAT_HTML){
						if (!empty($response->data)){
							$response->data = self::compress($response->data);
						}
						if (!empty($response->content)){
							$response->content = self::compress($response->content);
						}
					}
				});
		}

		if (!Yii::$app->request->isAjax){
			if ($favicon = $this->setting->site_favicon){
				$this->registerLinkTag([
					'rel'  => 'shortcut icon',
					'href' => $favicon
				]);

				$this->registerLinkTag([
					'rel'  => 'apple-touch-icon image_src',
					'href' => $favicon
				]);
			}

			if (!empty($this->setting->social_image)){
				$this->registerMetaTag([
					'name'    => 'og:image',
					'content' => $this->setting->social_image
				], 'og:image');
			}
		}
	}

	/**
	 * HTML compress function.
	 *
	 * @param $html
	 *
	 * @return mixed
	 */
	public static function compress($html){
		$filters = [
			// remove javascript comments
			'/(?:<script[^>]*>|\G(?!\A))(?:[^\'"\/<]+|"(?:[^\\"]+|\\.)*"|\'(?:[^\\\']+|\\.)*\'|\/(?!\/)|<(?!\/script))*+\K\/\/[^\n|<]*/xsu' => '',
			// remove html comments except IE conditions
			'/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/su'                                                                          => '',
			// remove comments in the form /* */
			'/\/+?\s*\*[\s\S]*?\*\s*\/+/u'                                                                                                  => '',
			// shorten multiple white spaces
			'/>\s{2,}</u'                                                                                                                   => '><',
			// shorten multiple white spaces
			'/\s{2,}/u'                                                                                                                     => ' ',
			// collapse new lines
			'/(\r?\n)/u'                                                                                                                    => '',
		];

		return preg_replace(array_keys($filters), array_values($filters), $html);
	}
}