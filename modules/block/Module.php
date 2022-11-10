<?php

namespace modules\block;

use modules\BaseModule;
use modules\block\blocks\CustomHtml;
use modules\block\blocks\Html;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class Module
 *
 * @package modules\block
 */
class Module extends BaseModule{

	/**
	 * @param \yii\base\Application $app
	 */
	public function bootstrap($app){
		parent::bootstrap($app);

		$app->params['block.positions'] = [
			'homepage' => Yii::t('block', 'Homepage'),
			'footer'   => Yii::t('block', 'Footer'),
		];

		$blocks = [
			'raw_html' => [
				'class' => Html::class,
				'name'  => Yii::t('block', 'Widget HTML')
			],
			'html'     => [
				'class' => CustomHtml::class,
				'name'  => Yii::t('block', 'Custom Block HTML')
			],
		];

		$app->params['block.types'] = ArrayHelper::merge($app->params['block.types'] ?? [],
			$blocks);
	}
}