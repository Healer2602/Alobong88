<?php
/**
 * @var \yii\web\View $this
 * @var \backend\models\UserForm $model
 */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = Yii::t('app', 'My Profile');

?>

<div class="row justify-content-center">
	<div class="col-xl-5 col-lg-6">
		<div class="m-portlet">
			<?php $form = ActiveForm::begin([
				'id' => 'my-profile',
			]); ?>

			<?= $form->field($model, 'username')->textInput(['disabled' => TRUE]) ?>

			<?= $form->field($model, 'email')->textInput(['disabled' => TRUE]) ?>

			<?= $form->field($model, 'name') ?>

			<?= $form->field($model, 'password')->passwordInput()->hint(Yii::t('common',
				'Leave password blank if you don\'t want to change.')) ?>

			<?= $form->field($model, 'confirm_password')->passwordInput() ?>

			<div class="form-group">
				<?= Html::submitButton(Yii::t('common', 'Update'),
					['class' => 'btn btn-primary']) ?>
			</div>

			<?php ActiveForm::end(); ?>
		</div>
	</div>
</div>
