<?php

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/** @var \modules\customer\models\CustomerRank $model */

?>

<?php $form = ActiveForm::begin([
	'id' => 'customer_rank',
]); ?>

<?= $form->field($model, 'name') ?>

<?= $form->field($model, 'description')->textarea(['rows' => 4]) ?>

<?= $form->field($model, 'type')
         ->dropDownList($model->types, ['data-toggle' => 'select']) ?>

<?= $form->field($model, 'daily_limit_balance')->begin() ?>
<?= Html::activeLabel($model, 'daily_limit_balance', ['class' => 'form-label']) ?>
<div class="input-group input-group-merge">
	<?= Html::activeTextInput($model, 'daily_limit_balance',
		['class' => 'form-control form-control-prepended']) ?>

	<div class="input-group-text rounded-end">
		<span class="fe fe-dollar-sign"></span>
	</div>

	<?= Html::error($model, 'daily_limit_balance',
		['class' => 'invalid-feedback']) ?>
</div>
<?= $form->field($model, 'daily_limit_balance')->end() ?>

<?= $form->field($model, 'withdraw_limit_balance')->begin() ?>
<?= Html::activeLabel($model, 'withdraw_limit_balance', ['class' => 'form-label']) ?>
<div class="input-group input-group-merge">
	<?= Html::activeTextInput($model, 'withdraw_limit_balance',
		['class' => 'form-control form-control-prepended']) ?>

	<div class="input-group-text rounded-end">
		<span class="fe fe-dollar-sign"></span>
	</div>

	<?= Html::error($model, 'withdraw_limit_balance',
		['class' => 'invalid-feedback']) ?>
</div>
<?= $form->field($model, 'withdraw_limit_balance')->end() ?>

<?= $form->field($model, 'daily_count_balance') ?>

<?= $form->field($model, 'is_default')->checkbox() ?>

<div class="form-group">
	<?= Html::submitButton(Yii::t('common',
		$model->isNewRecord ? 'Create' : 'Update'),
		['class' => 'btn btn-primary w-100']) ?>

	<?= Html::a(Yii::t('common', 'Cancel'), ['#'],
		['class' => 'btn btn-link text-muted w-100', 'data-bs-dismiss' => 'modal']) ?>
</div>

<?php ActiveForm::end(); ?>
