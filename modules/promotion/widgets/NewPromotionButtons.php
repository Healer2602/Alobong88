<?php

namespace modules\promotion\widgets;

use modules\promotion\models\Promotion;
use Yii;
use yii\bootstrap5\ButtonDropdown;
use yii\bootstrap5\Widget;

/**
 * New Promotion Buttons
 */
class NewPromotionButtons extends Widget{

	/**
	 * @throws \Exception
	 */
	public function run()
	: string{
		$items = [];
		foreach (Promotion::types() as $type => $label){
			$items[] = [
				'label' => $label,
				'url'   => ['create', 'type' => $type]
			];
		}

		return ButtonDropdown::widget([
			'label'         => Yii::t('promotion', 'New Promotion'),
			'dropdown'      => [
				'items' => $items
			],
			'buttonOptions' => ['class' => 'btn btn-primary btn-new']
		]);
	}
}