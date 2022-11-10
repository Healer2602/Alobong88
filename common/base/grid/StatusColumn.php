<?php

namespace common\base\grid;

use Closure;
use Yii;
use yii\grid\Column;
use yii\helpers\Html;
use yii\helpers\Url;
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
 *         'class' => 'yii\grid\StatusColumn',
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
class StatusColumn extends Column{

	/**
	 * {@inheritdoc}
	 */
	public $header = 'Status';

	public $attribute = 'status';

	public $action = [];

	public $active_value;

	public $readonly = FALSE;

	/**
	 * @inheritDoc
	 */
	public function init(){
		parent::init();

		$this->header = Yii::t('common', $this->header);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function renderDataCellContent($model, $key, $index){
		$visible = $this->visible;
		if ($this->visible instanceof Closure){
			$visible = call_user_func($this->visible, $model);
		}

		if ($this->action instanceof Closure){
			$this->action = call_user_func($this->action, $model);
		}

		if (!$visible){
			return NULL;
		}

		if ($this->action){
			$action = Url::to($this->action + ['id' => $model->id]);
			$this->_registerJs();
		}else{
			$action = '';
		}

		if (!empty($this->active_value)){
			$is_checked = $model->{$this->attribute} == $this->active_value;
		}else{
			$is_checked = ($model->{$this->attribute} == 10 || $model->{$this->attribute} == 1);
		}

		$checkbox = Html::checkbox('status', $is_checked,
			['class' => 'form-check-input status-switcher', 'id' => "switcher-column-{$index}", 'disabled' => $this->readonly]);

		$checkbox .= Html::label('', "switcher-column-{$index}",
			['class' => 'form-check-label', 'data-action' => $action]);

		return Html::tag('div', $checkbox, ['class' => 'form-check form-switch']);
	}

	/**
	 * @inheritDoc
	 */
	private function _registerJs(){
		$js = <<< JS
			$(document).on('change', '.status-switcher', function(){
				var action = $(this).siblings('label').data('action');
				if(action){
					$.post(action);
				}
			});
JS;

		$this->grid->view->registerJs($js, View::POS_READY, 'status-switcher');
	}
}
