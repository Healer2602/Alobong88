<?php

namespace backend\base;

use Closure;
use yii\grid\CheckboxColumn as CheckboxColumnBase;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * Class CheckboxColumn
 *
 * @package backend\base
 */
class CheckboxColumn extends CheckboxColumnBase{

	public $cssClass = 'form-check-input.checbox-column';

	public $is_header = TRUE;

	/**
	 * Renders the header cell content.
	 * The default implementation simply renders [[header]].
	 * This method may be overridden to customize the rendering of the header cell.
	 *
	 * @return string the rendering result
	 */
	protected function renderHeaderCellContent(){
		if (!$this->is_header){
			return '';
		}

		if ($this->header !== NULL || !$this->multiple){
			return trim($this->header) !== '' ? $this->header : $this->getHeaderCellLabel();
		}

		$content = Html::beginTag('div', ['class' => 'custom-control form-check']);
		$content .= Html::checkbox($this->getHeaderCheckBoxName(), FALSE,
			['class' => 'select-on-check-all form-check-input', 'id' => 'cc-check-all']);
		$content .= Html::label('', 'cc-check-all', ['class' => 'form-check-label']);
		$content .= Html::endTag('div');

		$this->headerOptions['class'][] = 'checkbox-column';

		return $content;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function renderDataCellContent($model, $key, $index){
		$checkbox = $this->grid->options['id'] . "-cc-input-{$index}";
		if (!is_a($this->checkboxOptions, 'Closure')){
			$this->checkboxOptions['id'] = $checkbox;
		}

		$content = Html::beginTag('div', ['class' => 'form-check']);
		$content .= $this->renderDataCellInput($model, $key, $index);
		$content .= Html::label('', $checkbox, ['class' => 'form-check-label']);
		$content .= Html::endTag('div');

		return $content;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function renderDataCellInput($model, $key, $index){
		if ($this->content !== NULL){
			return call_user_func($this->content, $model, $key, $index, $this);
		}

		if ($this->checkboxOptions instanceof Closure){
			$options = call_user_func($this->checkboxOptions, $model, $key, $index, $this);
		}else{
			$options = $this->checkboxOptions;
		}

		if (!isset($options['value'])){
			$options['value'] = is_array($key) ? Json::encode($key) : $key;
		}

		if ($this->cssClass !== NULL){
			Html::addCssClass($options, str_replace(".", " ", $this->cssClass));
		}

		$options['id'] = $this->grid->options['id'] . "-cc-input-{$index}";

		return Html::checkbox($this->name, !empty($options['checked']), $options);
	}
}