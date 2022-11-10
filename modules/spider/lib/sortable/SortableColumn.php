<?php

namespace modules\spider\lib\sortable;

use yii\grid\Column;
use yii\helpers\Html;
use yii\web\View;

/**
 * StatusColumn displays a status of row.
 *
 * To add a SerialColumn to the [[GridView]], add it to the [[GridView::columns|columns]]
 * configuration as follows:
 *
 * ```php
 * 'columns' => [
 *     // ...
 *     [
 *         'class' => 'modules\spider\lib\sortable\SortableColumn',
 *         // you may configure additional properties here
 *     ],
 * ]
 * ```
 *
 * For more details and usage information on StatusColumn, see the [guide article on data
 * widgets](guide:output-data-widgets).
 *
 * @author Giang
 * @since 2.0
 */
class SortableColumn extends Column{

	/**
	 * {@inheritdoc}
	 */
	public $header = '';

	public $options;

	/**
	 * @inheritDoc
	 */
	public function init(){
		parent::init();

		SortableAsset::register($this->grid->view);
	}

	/**
	 * @return string
	 */
	protected function renderHeaderCellContent(){
		$this->headerOptions['class'][] = 'sortable-column';

		return parent::renderHeaderCellContent();
	}

	/**
	 * {@inheritdoc}
	 */
	protected function renderDataCellContent($model, $key, $index){
		$this->_registerJs();

		$result = '<span class="sortable-handler"><span class="fe fe-move"></span></span>';
		$result .= Html::checkbox('cid[]', FALSE,
			['value' => $model->id, 'class' => 'd-none']);

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	private function _registerJs(){
		$js = <<<JS
		new $.JSortableList('#sortableList tbody', 'sortableList', 'asc', $('#sortableList').attr('action'), '', '1');
JS;

		$this->grid->view->registerJs($js, View::POS_READY, 'sortable');
	}
}
