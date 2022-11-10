<?php

/**
 * @var frontend\base\View $this
 * @var yii\bootstrap5\ActiveForm $form
 * @var \modules\customer\frontend\models\KycForm $model
 */

use modules\customer\widgets\Menu;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

$this->title = Yii::t('customer', 'Change password');
?>

<div class="customer-dashboard kyc py-4 default-bg">
	<div class="container">
		<div class="row">
			<div class="col-lg-auto mb-lg-0 mb-4">
				<div class="menu-account">
					<?= Menu::widget() ?>
				</div>
			</div>
			<div class="col-lg">
				<div class="main-form">
					<h1 class="heading text-large"><?= Html::encode($this->title) ?></h1>
					<div class="row justify-content-center">
						<div class="col-lg-9">
							<?php $form = ActiveForm::begin(['id' => 'change-password-form']); ?>
							<div class="row mb-3">
								<div class="col-lg-3 mb-lg-0">
									<?= Html::activeLabel($model, 'current_password',
										['class' => 'my-2']) ?>
								</div>
								<div class="col-lg-9">
									<?= $form->field($model, 'current_password')
									         ->passwordInput([
										         'class'       => 'form-control',
										         'placeholder' => Yii::t('customer',
											         'Enter current password')
									         ])->label(FALSE) ?>
								</div>
							</div>
							<div class="row mb-3">
								<div class="col-lg-3 mb-lg-0 mb-2">
									<?= Html::activeLabel($model, 'password',
										['class' => 'my-2']) ?>
								</div>
								<div class="col-lg-9">
									<?= $form->field($model, 'password')
									         ->passwordInput([
										         'class'       => 'form-control',
										         'placeholder' => Yii::t('customer',
											         'Enter new password')
									         ])->label(FALSE) ?>
								</div>
							</div>
							<div class="row mb-3">
								<div class="col-lg-3 mb-lg-0 mb-2">
									<?= Html::activeLabel($model, 'confirm_password',
										['class' => 'my-2']) ?>
								</div>
								<div class="col-lg-9">
									<?= $form->field($model, 'confirm_password')
									         ->passwordInput([
										         'class'       => 'form-control',
										         'placeholder' => Yii::t('customer',
											         'Confirmation password')
									         ])->label(FALSE) ?>
								</div>
							</div>
							<div class="row justify-content-end">
								<div class="col-lg-9">
									<button type="submit" class="btn btn-round btn-primary btn-wide"><?= Yii::t('customer',
											'Confirm') ?></button>
								</div>
							</div>
							<?php ActiveForm::end(); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>