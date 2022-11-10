<?php

namespace modules\media\widgets;

use modules\media\MediaInputAssets;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\InputWidget;

/**
 * MediaInputModal is the base class for widgets that collect file inputs.
 *
 * An input widget can be associated with a data [[model]] and an [[attribute]],
 * or a [[name]] and a [[value]]. If the former, the name and the value will
 * be generated automatically (subclasses may call [[renderInputHtml()]] to follow this behavior).
 *
 * Classes extending from this widget can be used in an [[\yii\widgets\ActiveForm|ActiveForm]]
 * using the [[\yii\widgets\ActiveField::widget()|widget()]] method, for example like this:
 *
 * ```php
 * $form->field($model, 'thumbnail')->widget(MediaInputModal::class, [
 *      'type'         => MediaInputModal::TYPE_IMAGE,
 *      'current_path' => 'news'
 * ])
 * ```
 *
 * @author Giang
 * @since 1.0
 */
class MediaInputModal extends InputWidget{

	const TYPE_IMAGE = 0;

	const TYPE_FILE = 2;

	const TYPE_VIDEO = 3;

	const TYPE_ALL = - 1;

	/**
	 * @var string
	 */
	public $modalTitle = 'Media Manager';

	/**
	 * @var string
	 */
	public $inputId;

	/**
	 * @var array
	 */
	public $inputOptions = ['class' => 'form-control'];

	/**
	 * @var array
	 */
	public $mediaOptions = [];

	/**
	 * @var string
	 */
	public $buttonLabel = 'Browse';

	/**
	 * @var string
	 */
	public $current_path = '';

	/**
	 * @var boolean
	 */
	public $is_preview = TRUE;

	/**
	 * @var array
	 */
	private $_selected = [];

	/**
	 * @var bool
	 */
	private $_is_multiple = FALSE;

	/**
	 * @var null|string
	 */
	public $target = NULL;

	/**
	 * @var boolean
	 */
	public $disabled = FALSE;

	/**
	 * @var integer for media type like Image, Video or File
	 */
	public $type = 0;

	/**
	 * @inheritdoc
	 * @throws \yii\base\InvalidConfigException
	 */
	public function init(){
		parent::init();
		$attributes = NULL;
		MediaInputAssets::register($this->view);

		if ($this->hasModel()){
			$this->inputId = Html::getInputId($this->model, $this->attribute);

			if (!empty($this->model->{$this->attribute})){
				$attributes = $this->model->{$this->attribute};
			}
		}else{
			$this->inputId      = $this->getId() . '-input';
			$this->inputOptions = array_merge($this->inputOptions, [
				'id' => $this->inputId
			]);
			$attributes         = $this->value;
		}

		if (!empty($attributes)){
			if (!empty($this->mediaOptions['multiple'])){
				$attributes = Json::decode($attributes);
			}else{
				$attributes = [$attributes];
			}

			$this->_selected = ArrayHelper::map($attributes, function ($data){
				return basename($data);
			}, function ($data){
				return $data;
			});
		}

		$this->mediaOptions['selected'] = base64_encode(Json::encode(array_keys($this->_selected)));

		$type = NULL;
		if ($this->type == self::TYPE_IMAGE){
			$type = 'Images';
		}elseif ($this->type == self::TYPE_VIDEO){
			$type = 'Videos';
		}elseif ($this->type == self::TYPE_FILE){
			$type = 'Files';
		}

		$this->mediaOptions = array_merge([
			'/media/manager/dialog',
			'field_id' => $this->inputId,
			'fldr'     => $this->current_path,
			'multiple' => 0,
			'type'     => $type
		], $this->mediaOptions);

		$this->_is_multiple = !empty($this->mediaOptions['multiple']);
	}

	/**
	 * @inheritdoc
	 */
	public function run(){
		echo Html::beginTag('div', ['class' => 'media-box']);

		if ($this->is_preview){
			echo $this->renderPreview();
		}
		echo $this->renderInputGroup();
		if (empty($this->target)){
			echo $this->renderModal();
		}
		echo Html::endTag('div');
	}

	/**
	 * @return string
	 */
	public function renderPreview(){
		$html = Html::beginTag('div', ['class' => 'media-preview']);

		if ($attributes = $this->_selected){
			$image_ext = ["gif", "jpg", "jpeg", "png", "svg"];
			foreach ($attributes as $attribute){
				$file_info = pathinfo($attribute);
				$extension = strtolower($file_info['extension']);
				if (!empty($extension) && ArrayHelper::isIn($extension,
						$image_ext)){
					$file = Html::tag('div', Html::img($attribute), ['class' => 'file image']);
				}else{
					$file = Html::beginTag('div',
						['class' => ['file', $extension ?? NULL]]);
					$file .= Html::tag('span', $extension ?? '', ['class' => 'type']);
					$file .= Html::tag('span', $file_info['basename'] ?? '', ['class' => 'name']);
					$file .= Html::endTag('div');
				}

				$html .= Html::tag('div', $file, ['class' => 'media-item']);
			}
		}

		return $html . Html::endTag('div');
	}

	/**
	 * @return string
	 */
	public function renderInput(){
		$this->inputOptions['readonly'] = 'readonly';
		$this->inputOptions['class']    = array_merge([$this->inputOptions['class']],
			['media-input']);

		$input = '';
		if ($this->_is_multiple){
			$form_message = '';
			if (!empty($this->_selected)){
				$form_message = Yii::t('common', '{0} file(s) selected',
					[count($this->_selected)]);
			}

			$input = Html::textInput('', $form_message,
				['class' => 'form-control form-message', 'disabled' => TRUE]);
		}

		if ($this->hasModel()){
			if ($this->_is_multiple){
				$input .= Html::activeHiddenInput($this->model, $this->attribute,
					$this->inputOptions);
			}else{
				$input = Html::activeTextInput($this->model, $this->attribute, $this->inputOptions);
			}
		}else{
			if ($this->_is_multiple){
				$input .= Html::hiddenInput($this->name, $this->value,
					$this->inputOptions);
			}else{
				$input .= Html::textInput($this->name, $this->value, $this->inputOptions);
			}
		}

		return $input;
	}

	/**
	 * @return string
	 */
	public function renderInputGroup(){
		$buttonIcon  = '<i class="fe fe-folder me-2" aria-hidden="true"></i>';
		$buttonLabel = $buttonIcon . ' ' . Yii::t('common', $this->buttonLabel);

		$button = Html::button('<i class="fe fe-trash-2 me-2"></i>' . Yii::t('common',
				'Remove'),
			['class' => ['btn btn-secondary btn-remove rounded-0'], 'disabled' => $this->disabled]);

		$button .= Html::a($buttonLabel, $this->mediaOptions, [
			'data-toggle' => 'media-modal',
			'data-target' => $this->target ?? '#' . $this->getModalId(),
			'class' => ['btn btn-primary ms-0 btn-browse rounded-0 rounded-end', $this->disabled ? 'disabled' : NULL]
		]);

		$button = Html::tag('div', $button, ['class' => 'input-group-append']);
		$input  = $this->renderInput();

		return Html::tag('div', $input . $button, ['class' => 'input-group media-input-group']);
	}

	/**
	 * @return string
	 * @throws \Exception
	 */
	public function renderModal(){
		return MediaModal::widget([
			'id'         => $this->getModalId(),
			'modalTitle' => $this->modalTitle
		]);
	}

	/**
	 * @return string
	 */
	public function getModalId(){
		return $this->getId() . '-media-modal';
	}
}
