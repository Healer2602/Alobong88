<?php

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/**
 * @var \backend\base\View $this
 * @var \backend\models\UserForm $model
 */
?>

<div class="row justify-content-center">

	<div class="col-lg-7 col-xl-5">
		<?php $form = ActiveForm::begin([
			'id' => 'user_form'
		]); ?>

		<?= $form->field($model, 'username') ?>

		<?= $form->field($model, 'email') ?>

		<?= $form->field($model, 'name') ?>

		<?= $form->field($model, 'password')->passwordInput() ?>

		<?= $form->field($model, 'confirm_password')->passwordInput() ?>

		<?= $form->field($model, 'user_group_id')
		         ->dropDownList($model->groups,
			         ['class' => 'form-select', 'data-toggle' => 'select', 'multiple' => TRUE]) ?>

		<div class="form-group">
			<?= Html::submitButton(Yii::t('common',
				$model->isNewRecord ? 'Create' : 'Update'),
				['class' => 'btn btn-primary w-100']) ?>

			<?= Html::a(Yii::t('common', 'Cancel'), ['index'],
				['class' => 'btn btn-link text-muted w-100']) ?>
		</div>

		<?php ActiveForm::end(); ?>
	</div>

</div>
