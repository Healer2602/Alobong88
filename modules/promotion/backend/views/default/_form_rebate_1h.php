<?php
/**
 * @var yii\web\View $this
 * @var \modules\promotion\models\Promotion $model
 * @var yii\bootstrap5\ActiveForm $form
 */

use modules\promotion\models\Promotion;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

?>

<div class="promotion-form">

	<?php $form = ActiveForm::begin(); ?>

	<?= $form->field($model, 'type')
	         ->textInput(['maxlength' => TRUE, 'class' => 'form-control form-control-lg', 'value' => $model->typeLabel, 'disabled' => TRUE]) ?>

	<?= $form->field($model, 'name')
	         ->textInput(['maxlength' => TRUE, 'class' => 'form-control form-control-lg']) ?>

	<?= $form->field($model, 'date')
	         ->textInput([
		         'class'          => 'form-control form-control-lg',
		         'data-flatpickr' => '{"mode": "range"}'
	         ]) ?>

	<?= $form->beginField($model, 'bonus_rate',
		['options' => ['class' => 'form-group']]) ?>
	<?= Html::activeLabel($model, 'bonus_rate', ['class' => 'form-label']) ?>
	<div class="input-group input-group-merge">
		<?= Html::activeTextInput($model, 'bonus_rate', ['class' => 'form-control']) ?>
		<div class="input-group-text rounded-end">
			<span class="fe fe-percent"></span>
		</div>
		<?= Html::error($model, 'bonus_rate', ['class' => 'invalid-feedback']) ?>
	</div>
	<?= $form->endField() ?>

	<?= $form->beginField($model, 'min_bonus',
		['options' => ['class' => 'form-group']]) ?>
	<?= Html::activeLabel($model, 'min_bonus', ['class' => 'form-label']) ?>
	<div class="input-group input-group-merge">
		<?= Html::activeTextInput($model, 'min_bonus',
			['class' => 'form-control', 'type' => 'number', 'min' => 1]) ?>
		<div class="input-group-text rounded-end">
			<span class="fe fe-dollar-sign"></span>
		</div>
		<?= Html::error($model, 'min_bonus', ['class' => 'invalid-feedback']) ?>
	</div>
	<?= $form->endField() ?>

	<?= $form->field($model, 'excluding_revenue')
	         ->checkboxList($model->excludingRevenues) ?>

	<?= $form->field($model, 'exclude_promotion')
	         ->checkboxList($model->excludePromotions) ?>

	<?= $form->field($model, 'product_wallet')
	         ->checkboxList($model->productWallets) ?>

	<?= $form->field($model, 'status')
	         ->dropDownList(Promotion::statuses()) ?>

	<div class="form-group mt-4">
		<?= Html::submitButton(Yii::t('common',
			$model->isNewRecord ? 'Create' : 'Update'),
			['class' => 'btn btn-primary d-block w-100']) ?>

		<?= Html::a(Yii::t('common', 'Cancel'), ['index'],
			['class' => 'btn btn-link text-muted d-block w-100']) ?>
	</div>

	<?php ActiveForm::end(); ?>
</div>