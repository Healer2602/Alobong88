<?php

/**
 * @var frontend\base\View $this
 * @var yii\bootstrap5\ActiveForm $form
 * @var \modules\customer\frontend\models\KycForm $model
 * @var \modules\customer\frontend\models\KycForm $model_email
 */

use common\widgets\Alert;
use modules\customer\widgets\AccountHeader;
use modules\customer\widgets\Menu;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

$this->title = Yii::t('customer', 'Account verification');
?>

	<div class="customer-dashboard kyc py-4 default-bg">
		<div class="container">
			<?= AccountHeader::widget() ?>

			<div class="row">
				<div class="col-lg-auto mb-lg-0 mb-4">
					<div class="menu-account">
						<?= Menu::widget() ?>
					</div>
				</div>
				<div class="col-lg">

					<?= Alert::widget() ?>

					<div class="main-form">
						<h1 class="heading text-large"><?= Html::encode($this->title) ?></h1>

						<?= $this->render('_kyc_id', [
							'model' => $model
						]); ?>

						<?php if (!empty($model->user->isEmailVerified)){
							echo \yii\bootstrap5\Alert::widget([
								'body'        => Html::tag('p',
										'<i class="las la-info la-3x"></i>',
										['class' => 'text-center mb-1']) . Yii::t('customer',
										'Your email has been verified.'),
								'options'     => [
									'class' => 'alert-success text-center d-flex align-items-center my-4'
								],
								'closeButton' => FALSE
							]);
						} ?>

						<?php if (!empty($model_email)): ?>
							<hr>
							<h3 class="text-large text-white my-4"><?= Yii::t('customer',
									'Verify Your Email Address') ?></h3>

							<?php $form = ActiveForm::begin([
								'id' => 'verify-form'
							]); ?>
							<div class="col-lg-6 mb-4">
								<small class="d-block mb-2"><?= Yii::t('customer',
										'You are required to verify your email address. Key in your email address in the field provided below to receive a "Verification Link" or "Verification Code".') ?></small>

								<?= $form->field($model_email, 'email')
								         ->textInput(['class' => 'mt-3 form-control', 'readonly' => $model_email->scenario === $model_email::SCENARIO_EMAIL_CODE])
								         ->label(FALSE) ?>

								<?php if ($model_email->scenario === $model_email::SCENARIO_EMAIL_CODE){
									echo $form->field($model_email, 'email_code')
									          ->textInput(['class' => 'mt-3 form-control', 'placeholder' => $model_email->getAttributeLabel('email_code')])
									          ->label(FALSE);
								} ?>

								<button type="submit" class="btn btn-round btn-primary btn-wide"><?= Yii::t('common',
										'Submit') ?></button>

								<?php if ($model_email->scenario === $model_email::SCENARIO_EMAIL_CODE){
									echo Html::a(Yii::t('common', 'Resend'), ['resend'],
										['class' => 'btn btn-round btn-outline-primary btn-wide']);
								} ?>

								<?= Html::activeHiddenInput($model_email, 'verify') ?>
							</div>
							<?php ActiveForm::end(); ?><?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>

<?php
$js = <<< JS
	$('.child-form-group .btn.remove').on('click', function(){
	    $(this).parents('.child-form-group').find('.preview-image').html('');
	    $(this).parents('.child-form-group').find('.form-control-file').val('');
	    $(this).parent().addClass('d-none');
	});

	$(".custom-file-input").on('change', function () {
	    readURL(this);
	});
	
	function readURL(input) {
        let preview = $(input).parents('.form-group').find('.preview-image');
        preview.text('');

        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                let img = $('<img>').attr('src', e.target.result);
                preview.html(img);
                preview.siblings().removeClass('d-none');
            };

            reader.readAsDataURL(input.files[0]);
        }
    }
JS;
$this->registerJs($js);