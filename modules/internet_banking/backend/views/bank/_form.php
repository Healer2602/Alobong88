<?php

use modules\media\widgets\MediaInputModal;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/**
 * @var \yii\web\View $this
 * @var \modules\internet_banking\models\Bank $model
 */

?>
	<div class="form-bank">
		<?php $form = ActiveForm::begin(['id' => 'form-bank']); ?>

		<?= $form->field($model, 'bank_id')
		         ->dropDownList($model->banks, ['class' => 'form-select']) ?>

		<?= $form->field($model, 'name') ?>

		<?= $form->field($model, 'logo')
		         ->widget(MediaInputModal::class, [
			         'current_path' => 'bank',
			         'target'       => '#media-modal'
		         ]) ?>

		<?= $form->field($model, 'code') ?>

		<?= $form->field($model, 'currency_code')
	         ->dropDownList($model->currencies,
		         [
			         'class'       => 'form-select',
			         'data-toggle' => 'select',
			         'prompt'      => Yii::t('common', 'Select')
		         ]) ?>

	<?= $form->field($model, 'account_id') ?>
	<?= $form->field($model, 'account_name') ?>
	<?= $form->field($model, 'account_branch') ?>

	<?= $form->field($model, 'status')
	         ->dropDownList($model->statuses,
		         ['class' => 'form-select', 'data-toggle' => 'select']) ?>

	<div class="form-group mt-4">
		<?= Html::submitButton(Yii::t('common', $model->isNewRecord ? 'Create' : 'Update'),
			['class' => 'btn btn-primary w-100']) ?>

		<?= Html::a(Yii::t('common', 'Cancel'), ['#'],
			['class' => 'btn btn-link w-100 text-muted', 'data-bs-dismiss' => "modal"]) ?>
	</div>

		<?php ActiveForm::end(); ?>
	</div>

<?php

$js = <<<JS
	$.select2($('#form-bank .form-select'));
JS;
$this->registerJs($js);