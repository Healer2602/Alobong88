<?php
/**
 * @var \yii\web\View $this
 * @var \modules\customer\models\Customer $model
 */

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

?>

<?php $form = ActiveForm::begin(); ?>

<?= $form->field($model, 'customer_rank_id')
         ->dropDownList($model->ranks,
	         ['data-toggle' => 'select', 'prompt' => Yii::t('customer', 'Default')]) ?>

<?= $form->field($model, 'username') ?>

<?= $form->field($model, 'name')
         ->textInput()
         ->label($model->getAttributeLabel('name') . ($model->isVerified ? ' <i class="fe fe-check-circle text-success"></i>' : '')) ?>

<?= $form->field($model, 'email')
         ->textInput()
         ->label($model->getAttributeLabel('email') . ($model->isEmailVerified ? ' <i class="fe fe-check-circle text-success"></i>' : '')) ?>

<?= $form->field($model, 'phone_number') ?>

<?= $form->field($model, 'currency')->dropDownList($model->currencies, [
	'data-toggle' => 'select'
]) ?>

<?= $form->field($model, 'status')
         ->dropDownList($model->statuses, ['data-toggle' => 'select']) ?>

	<div class="form-group">
		<?= Html::submitButton(Yii::t('common',
			$model->isNewRecord ? 'Create' : 'Update'),
			['class' => 'btn btn-primary w-100']) ?>

		<?= Html::a(Yii::t('common', 'Cancel'), ['index'],
			['class' => 'btn btn-link text-muted w-100']) ?>
	</div>

<?php ActiveForm::end(); ?>