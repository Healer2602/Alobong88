<?php

namespace backend\base;

use yii\bootstrap5\LinkPager;

/**
 * Class GridView
 *
 * @package backend\base
 *
 * @inheritDoc
 */
class GridView extends \yii\grid\GridView{

	/**
	 * @var bool
	 */
	public $batch_action = FALSE;

	/**
	 * @var array
	 */
	public $tableOptions = [
		'class' => 'table'
	];

	/**
	 * @var string
	 */
	public $layout = "{summary}<div class=\"table-responsive\">{items}</div>{pager}";

	/**
	 * @throws \yii\base\InvalidConfigException
	 * @throws \Exception
	 */
	public function init(){
		parent::init();

		$this->summary = SummaryWidget::widget([
			'dataProvider' => $this->dataProvider,
			'batch_action' => $this->batch_action
		]);

		$this->pager = [
			'class'                => LinkPager::class,
			'prevPageLabel'        => '<i class="fe fe-chevron-left"></i>',
			'nextPageLabel'        => '<i class="fe fe-chevron-right"></i>',
			'activePageCssClass'   => 'active',
			'disabledPageCssClass' => 'disabled',
			'nextPageCssClass'     => '',
			'prevPageCssClass'     => '',
		];
	}
}