<?php

/**
 * @var frontend\base\View $this
 * @var yii\bootstrap5\ActiveForm $form
 * @var \modules\customer\models\CustomerBank $model
 */

use modules\customer\frontend\models\CustomerIdentity;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

?>

<?php $form = ActiveForm::begin([
	'id'          => 'form-add-bank',
	'layout'      => ActiveForm::LAYOUT_HORIZONTAL,
	'fieldConfig' => [
		'horizontalCssClasses' => [
			'label'   => 'col-lg-3 mb-2 pt-1',
			'wrapper' => 'col-lg-9 mb-4',
		],
		'options'              => ['class' => 'row']
	],
]); ?>
	<div class="modal-body pb-0">
		<?= $form->field($model, 'bank_id')
		         ->dropDownList($model->banks,
			         ['class' => 'form-select select2', 'prompt' => Yii::t('customer',
				         'Select Bank')]) ?>

		<?= $form->field($model, 'account_branch')->textInput(['placeholder' => Yii::t('customer',
			'Enter bank branch')]) ?>

		<?= $form->field($model, 'account_name')->textInput([
			'placeholder' => Yii::t('customer', 'Enter bank name'),
			'value'       => CustomerIdentity::profile()->name ?? ''
		]) ?>

		<?= $form->field($model, 'account_id')->textInput(['placeholder' => Yii::t('customer',
			'Enter bank no')]) ?>
	</div>
	<div class="row justify-content-end">
		<div class="col-lg-9">
			<div class="modal-footer justify-content-start pt-0">
				<?= Html::submitButton(Yii::t('common',
					'Save'),
					['class' => 'btn btn-primary btn-round px-4']) ?>
			</div>
		</div>
	</div>
<?php ActiveForm::end();
$js = <<<JS
	$('.select2').select2({ dropdownParent: $('#modal-add-bank')});
	$("#form-add-bank").on("beforeSubmit", function (event) {
	var form = $(this);
	var formData = form.serialize();
	$.ajax({
		url: form.attr("action"),
		type: "POST",
		data: formData,
		success: function (data) {
			if(data.validate) {
				form.yiiActiveForm('updateMessages', data.validate, true);
			}
		},
		error: function () {
			console.log("Something went wrong");
		}
	});
	}).on('submit', function(e){
		e.preventDefault();
});
JS;
$this->registerJs($js);