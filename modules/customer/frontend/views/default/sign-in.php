<?php

/**
 * @var frontend\base\View $this
 * @var yii\bootstrap5\ActiveForm $form
 * @var \modules\customer\frontend\models\LoginForm $model
 */

use modules\spider\recaptcha\InputWidget;
use modules\themes\captain\AppAsset;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Alert;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;

$asset = AppAsset::register($this);
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

				<?php $form = ActiveForm::begin([
					'id'          => 'main-login-form',
					'options'     => ['class' => 'form-login-register'],
					'action'      => ['/customer/default/sign-in'],
					'fieldConfig' => [
						'options' => ['class' => 'mb-3 px-3']
					]
				]); ?>

				<div class="row custom-form justify-content-around">
					<div class="col-lg-6">
						<div class="form-box">
							<h3 class="heading"><?= Yii::t('customer', 'Account') ?></h3>

							<div class="div">
								<?php if ($errors = $model->getFirstError('captcha')){
									echo Alert::widget([
										'body'    => $errors,
										'options' => [
											'class' => 'alert-danger my-3',
										],
									]);
								}else{
									echo \common\widgets\Alert::widget();
								} ?>
							</div>

							<?= $form->field($model, 'username') ?>

							<?= $form->field($model, 'password')->passwordInput() ?>

							<div class="row pt-3">
								<div class="col-lg-6">
									<?= $form->field($model, 'rememberMe')->checkbox() ?>
								</div>
								<div class="col-lg-6 text-lg-end">
									<?= Html::a(Yii::t('customer',
										'Forgot Password?'), ['forgot-password']) ?>
								</div>
							</div>

							<?= $form->field($model, 'captcha', ['options' => ['class' => '']])
							         ->widget(InputWidget::class, [
								         'options' => ['id' => 'main-loginform-captcha']
							         ]) ?>
						</div>

						<button type="submit" class="btn btn-primary btn-round"><?= Yii::t('customer',
								'Login') ?></button>

						<small class="bordered">
							<?= Yii::t('customer',
								'If you have any trouble when log in, please contact with us via "{0}" for further assistance.',
								Yii::t('customer', '<a href="#">{0}</a>',
									Yii::t('customer', 'Online Support'))) ?>
						</small>
					</div>
				</div>

				<?php ActiveForm::end() ?>
			</div>
		</div>
	</div>
</div>
