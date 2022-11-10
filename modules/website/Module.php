<?php

namespace modules\website;

use common\models\Language;
use modules\BaseModule;
use yii\web\Application;


/**
 * Class Module
 *
 * @package modules\website
 */
class Module extends BaseModule{


	/**
	 * @param $app
	 *
	 * @return void
	 * @throws \Throwable
	 */
	public function bootstrap($app){
		parent::bootstrap($app);

		if ($app instanceof Application && $app->id == 'website'){
			$language = $app->request->get('lang');
			if (!empty($language) && strlen($language) == 2){
				Language::setDefault($language);
			}

			$app->language       = Language::current();
			$app->sourceLanguage = Language::currentSource();
		}
	}
}