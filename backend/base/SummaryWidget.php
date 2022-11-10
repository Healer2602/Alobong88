<?php

namespace backend\base;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Class SummaryWidget
 *
 * @package backend\base
 */
class SummaryWidget extends Widget{

	const PAGE_SIZE = 20;

	/**
	 * @var \yii\data\BaseDataProvider the required data provider for the view.
	 */
	public $dataProvider;

	/**
	 * @var bool
	 */
	public $batch_action = FALSE;

	/**
	 * @inheritDoc
	 */
	public function init(){
		parent::init();
		$this->registerAssets();

		$current_page_size = Yii::$app->request->get('per-page', self::PAGE_SIZE);

		$html = Html::beginTag('div',
			['class' => 'summary d-flex align-items-center']);

		if ($this->batch_action){
			$html .= Html::submitButton(Yii::t('common', 'Delete selected item(s)'),
				['class' => 'btn btn-outline-danger mr-2', 'style' => 'display: none']);
		}

		if ($this->dataProvider->pagination !== FALSE){
			$html .= Html::tag('div', Html::dropDownList('per-page',
				Url::current(['per-page' => $current_page_size]), [
					Url::current(['per-page' => 20])  => Yii::t('common', 'Show {0}', [20]),
					Url::current(['per-page' => 50])  => Yii::t('common', 'Show {0}', [50]),
					Url::current(['per-page' => 100]) => Yii::t('common', 'Show {0}', [100]),
				], ['class' => 'form-select', 'data-toggle' => 'select']),
				['class' => 'per-page pe-2']);
		}

		$html .= Html::tag('span',
			Yii::t('common',
				'Showing <b>{begin, number}-{end, number}</b> of <b>{totalCount, number}</b> {totalCount, plural, one{item} other{items}}.'),
			['class' => 'information ms-auto']);

		$html .= Html::endTag('div');

		echo $html;
	}

	protected function registerAssets(){
		$js = "";

		if ($this->dataProvider->pagination !== FALSE){
			$js .= <<< JS
		$('.per-page>select').on('change', function(){
		    location.href = $(this).val();
		});
JS;
		}

		if ($this->batch_action){
			$js .= <<< JS
		$('[name="selection[]"]').on('change', function(){
		    $(this).parents('form').find('button[type=submit]').show();
		});
JS;
		}

		if (!empty($js)){
			$this->getView()->registerJs($js);
		}
	}
}