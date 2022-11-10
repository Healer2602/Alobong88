<?php

/** @var $this frontend\base\View
 * @var $form yii\bootstrap5\ActiveForm
 * @var $model \modules\customer\frontend\models\ResetPasswordForm
 */

use common\widgets\Alert;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

?>

<?= $this->render('_header') ?>

<div class="default-bg">
	<div class="container">
		<div class="row custom-form justify-content-around">
			<div class="col-lg-6">
				<?php $form = ActiveForm::begin([
					'id'          => 'new-password-form',
					'options'     => ['class' => 'form-login-register'],
					'fieldConfig' => [
						'options' => ['class' => 'mb-3 px-3']
					]
				]); ?>

				<div class="form-box">
					<h1 class="heading"><?= $this->title ?></h1>

					<?= Alert::widget() ?>

					<p class="card-title mb-4"><?= $this->t('Please choose your new password') ?></p>

					<?= $form->field($model, 'password')
					         ->passwordInput(['autofocus' => TRUE]) ?>

					<?= $form->field($model, 'confirm_password')->passwordInput() ?>

					<p class="text-center">
						<?= Yii::t('customer',
							'Remember your password?') ?> <?= Html::a(Yii::t('customer', 'Sign In'),
							['sign-in']) ?>. </p>
				</div>

				<?= Html::submitButton(Yii::t('customer', 'Update'),
					['class' => 'btn btn-primary btn-round']) ?>

				<?php ActiveForm::end(); ?>
			</div>
		</div>
	</div>
</div>
