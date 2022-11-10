<?php

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/* @var yii\web\View $this */
/* @var \modules\customer\models\Referral $model */
/* @var yii\bootstrap5\ActiveForm $form */
?>

	<div class="referral-form">
		<?php $form = ActiveForm::begin(); ?>
		<div class="row justify-content-center">
			<div class="<?= $model->isNewRecord ? 'col-xl-5 col-lg-6' : 'col' ?>">
				<?php
				if ($model->isNewRecord){
					echo $form->field($model, 'customer_id')
					          ->dropdownList($model->customers,
						          ['class' => 'custom-select', 'data-toggle' => 'select']);
				}else{
					echo $form->field($model, 'customer_id')
					          ->textInput(['value' => $model->customer->email, 'disabled' => TRUE])
					          ->label('Email');
					echo Html::activeHiddenInput($model, 'customer_id');
				}
				?>

				<?= $form->field($model, 'code', [
					'inputTemplate' => '<div class="input-group">{input}<button class="btn btn-outline-secondary generate" type="button">Generate</button></div>'
				]) ?>

				<?= $form->beginField($model, 'commission',
					['options' => ['class' => 'form-group']]) ?>
				<?= Html::activeLabel($model, 'commission', ['class' => 'form-label']) ?>
				<div class="input-group input-group-merge">
					<?= Html::activeTextInput($model, 'commission', ['class' => 'form-control']) ?>
					<div class="input-group-text rounded-end">
						<span class="fe fe-percent"></span>
					</div>
					<?= Html::error($model, 'commission', ['class' => 'invalid-feedback']) ?>
				</div>
				<?= $form->endField() ?>

				<?= $form->field($model, 'active_users') ?>

				<div class="form-group">
					<?= Html::submitButton(Yii::t('common', 'Save'),
						['class' => 'btn btn-primary w-100']) ?>

					<?= Html::a(Yii::t('common', 'Cancel'), ['index'],
						['class' => 'btn btn-link text-muted w-100']) ?>
				</div>

			</div>
		</div>
		<?php ActiveForm::end(); ?>
	</div>

<?php
$js = <<<JS
$('.generate').on('click', function(){
   var coupon = generateReferral(6);
   $(this).parents('.form-group').find('.form-control').val(coupon);
});

function generateReferral(length) {
  var text = "";
  var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
  for (var i = 0; i < length; i++)
    text += possible.charAt(Math.floor(Math.random() * possible.length));
  return text;
}
JS;
$this->registerJs($js);
$css = <<<CSS
.invalid-feedback:not(:empty){
    display: block;
}
CSS;
$this->registerCss($css);