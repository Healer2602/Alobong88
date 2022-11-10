<?php

/**
 * @var $this frontend\base\View
 * @var $form yii\bootstrap\ActiveForm
 * @var $model \modules\customer\frontend\models\PasswordResetRequestForm
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
					'id'          => 'forgot-password-form',
					'options'     => ['class' => 'form-login-register'],
					'fieldConfig' => [
						'options' => ['class' => 'mb-3 px-3']
					]
				]); ?>

				<div class="form-box">

					<h1 class="heading"><?= $this->title ?></h1>

					<?= Alert::widget() ?>

					<div class="mb-4">
						<?= Yii::t('customer',
							'Please fill out your email. A link to reset password will be sent there.') ?>
					</div>

					<?= $form->field($model, 'email') ?>

					<div class="text-center"><?= Yii::t('customer',
							'Remember your password?') ?> <?= Html::a(Yii::t('customer',
							'Sign In'),
							['sign-in']) ?></div>
				</div>

				<?= Html::submitButton(Yii::t('customer', 'Reset password'),
					['class' => 'btn btn-primary btn-round']) ?>

				<?php ActiveForm::end(); ?>
			</div>
		</div>
	</div>
</div></div>