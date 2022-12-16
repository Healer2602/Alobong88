<?php

/**
 * @var frontend\base\View $this
 * @var yii\bootstrap5\ActiveForm $form
 * @var \modules\customer\frontend\models\RegisterForm $model
 */

use modules\spider\recaptcha\InputWidget;
use modules\themes\captain\AppAsset;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Alert;
use yii\bootstrap5\Nav;

$asset = AppAsset::register($this);
$this->params['bodyClasses'] = 'page customer-page'
?>

<?= $this->render('_header') ?>

<div class="default-bg">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-lg-10 home-tabs">
				<?= Nav::widget([
					'options' => ['class' => 'nav nav-tabs'],
					'items'   => [
						[
							'label' => Yii::t('customer', 'Register'),
							'url'   => ['/customer/default/register'],
						],
						[
							'label' => Yii::t('customer', 'Login'),
							'url'   => ['/customer/default/sign-in'],
						],
					]
				]) ?>

				<?php if ($errors = $model->getFirstError('captcha')){
					echo Alert::widget([
						'body'    => $errors,
						'options' => [
							'class' => 'alert-danger my-3',
						],
					]);
				} ?>

				<?php $form = ActiveForm::begin([
					'id'          => 'register-form',
					'options'     => ['class' => 'form-login-register'],
					'action'      => ['/customer/default/register'],
					'fieldConfig' => [
						'options' => ['class' => 'mb-3']
					]
				]); ?>

				<div class="row custom-form">
					<div class="col-lg-6">
						<div class="form-box">
							<h3 class="heading"><?= Yii::t('customer', 'Account') ?></h3>

							<?= $form->field($model, 'username') ?>

							<?= $form->field($model, 'password')->passwordInput() ?>

							<?= $form->field($model, 'confirm_password')->passwordInput() ?>

							<?= $form->field($model, 'currency')->dropDownList($model->currencies, [
								'class' => 'form-select'
							]) ?>
						</div>
					</div>
					<div class="col-lg-6">
						<div class="form-box">
							<h3 class="heading"><?= Yii::t('customer',
									'Personal Information') ?></h3>

							<?= $form->field($model, 'name') ?>

							<?= $form->field($model, 'email') ?>

							<?= $form->field($model, 'phone') ?>

							<?= $form->field($model, 'dob')
							         ->textInput([
								         'data-flatpickr' => [
									         'datetimeFormat' => 'DD/MM/YYYY',
								         ],
							         ]) ?>
						</div>
					</div>
					<small class="pb-4">
						<?= Yii::t('customer',
							'To complete account registration, please click "JOIN", this means that customer is over 18 years old that read and agree to our terms & conditions when joining.') ?>
						<br>
						<a href="#"><?= Yii::t('customer',
								'View details of Terms & Conditions.') ?></a>
					</small>

					<?= $form->field($model, 'captcha')->widget(InputWidget::class) ?>

					<button type="submit" class="btn btn-primary btn-round"><?= Yii::t('customer',
							'Submit') ?></button>

					<?php ActiveForm::end() ?>
				</div>
			</div>
		</div>
	</div>
</div>