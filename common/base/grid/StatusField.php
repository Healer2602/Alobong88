<?php

namespace common\base\grid;

use common\base\Status;
use yii\bootstrap5\Html;
use yii\bootstrap5\InputWidget;
use yii\widgets\ActiveField;

/**
 * Class StatusField
 *
 * @package common\base\grid
 */
class StatusField extends InputWidget{

	/**
	 * @return string
	 */
	public function run()
	: string{
		$this->options['class'] = 'form-check-input';

		if ($this->field instanceof ActiveField){
			$this->field->label(FALSE);
		}

		if (empty($this->checked)){
			$this->options['value'] = Status::STATUS_ACTIVE;
		}

		if ($this->hasModel()){
			$checkbox = Html::activeCheckbox($this->model, $this->attribute, $this->options);
		}else{
			$checkbox = Html::checkbox($this->name, $this->value, $this->options);
		}

		return Html::tag('div', $checkbox, ['class' => 'form-check form-switch']);
	}
}
