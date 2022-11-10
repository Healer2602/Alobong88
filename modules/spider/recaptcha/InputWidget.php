<?php

namespace modules\spider\recaptcha;

use Yii;
use yii\base\InvalidConfigException;
use yii\bootstrap5\Html;
use yii\web\View;
use yii\widgets\InputWidget as BaseInputWidget;

/**
 * Class ReCaptchaInputWidget
 *
 * @package modules\spider\recaptcha
 */
class InputWidget extends BaseInputWidget{

	public $site_key = NULL;

	/**
	 * @throws \yii\base\InvalidConfigException
	 */
	public function init(){
		if ($this->site_key === NULL){
			$this->site_key = Yii::$app->params['recaptcha']['site_key'] ?? NULL;

			if (empty($this->site_key)){
				throw new InvalidConfigException("Required reCAPTCHA key params aren't set.");
			}
		}

		$this->field->template = "{input}\n{error}";

		parent::init();
	}

	/**
	 * @return string
	 * @throws \yii\base\InvalidConfigException
	 */
	public function run(){
		$this->registerJs();

		return Html::activeHiddenInput($this->model, $this->attribute,
			array_merge(['value' => ''], $this->options));
	}

	/**
	 * @throws \yii\base\InvalidConfigException
	 */
	private function registerJs(){
		$this->view->registerJsFile('https://www.google.com/recaptcha/api.js?render=' . $this->site_key,
			[], 'recaptcha');
		$this->view->registerJsVar('grecaptcha_key', $this->site_key);

		$formId  = $this->field->form->id;
		$inputId = $this->options['id'] ?? Html::getInputId($this->model, $this->attribute);
		$action  = $this->model->formName();

		$jsCode = <<<JS
	grecaptcha.ready(function () {
		grecaptcha.execute(grecaptcha_key, {action: '{$action}'}).then(function (token) {
			$('#{$inputId}').val(token);
		});
	});
	
	$('#{$formId}').on('beforeSubmit', function () {
		if (!$('#{$inputId}').val()) {
			grecaptcha.ready(function () {
				grecaptcha.execute(grecaptcha_key, {action: '{$action}'}).then(function (token) {
					$('#{$inputId}').val(token);
					$('#{$formId}').submit();
				});
			});
			return false;
		}
		
		return true;
	});
JS;

		$this->view->registerJs($jsCode, View::POS_READY);
	}
}
