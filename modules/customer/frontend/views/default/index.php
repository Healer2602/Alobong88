<?php

/**
 * @var frontend\base\View $this
 * @var yii\bootstrap5\ActiveForm $form
 * @var \modules\customer\models\Customer $model
 */

use common\widgets\Alert;
use modules\customer\widgets\AccountHeader;
use modules\customer\widgets\Menu;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

$this->title = Yii::t('customer', 'Account management');
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
				<div class="main-form">
					<h1 class="heading text-large"><?= Html::encode($this->title) ?></h1>

					<?= Alert::widget() ?>

					<?php $form = ActiveForm::begin([
						'id'     => 'account-form',
						'action' => ['/customer/default/index'],
					]); ?>
					<div class="row mb-4">
						<div class="col-lg-6 mb-lg-0 mb-2">
							<ul class="box-round dark p-3 list-unstyled mb-0 h-100">
								<li class="inline py-2">
									<div class="title">
										<?= $model->getAttributeLabel('username') ?>
									</div>
									<div class="username">
										<?= Html::encode($model->username) ?>
									</div>
								</li>
								<li class="inline py-2">
									<div class="title">
										<?= $model->getAttributeLabel('email') ?>
									</div>
									<div class="email icon-check <?= (!empty($model->verify['email']) ?? $model->verify['email'] === TRUE) ? 'checked' : '' ?>">
										<?= Html::encode($model->email) ?>
									</div>
								</li>
								<li class="inline py-2">
									<div class="title">
										<?= $model->getAttributeLabel('currency') ?>
									</div>
									<div class="currency">
										<?= $model->currency ?>
									</div>
								</li>
							</ul>
						</div>
						<div class="col-lg-6">
							<ul class="box-round dark p-3 list-unstyled mb-0 h-100">
								<?php if (!empty($model->isVerified)): ?>
									<li class="inline py-2">
										<div class="title">
											<?= $model->getAttributeLabel('name') ?>
										</div>
										<div class="name icon-check checked">
											<?= Html::encode($model->name) ?>
										</div>
									</li>
								<?php else: ?>
									<li class="inline">
										<?= Html::activeLabel($model, 'name',
											['class' => 'my-2']) ?>
										<?= $form->field($model, 'name')
										         ->textInput(['class' => 'form-control'])
										         ->label(FALSE) ?>
									</li>
								<?php endif; ?>
								<li class="inline">
									<?= Html::activeLabel($model, 'phone_number',
										['class' => 'my-2']) ?>
									<?= $form->field($model, 'phone_number')
									         ->textInput(['class' => 'form-control'])
									         ->label(FALSE) ?>
								</li>
								<li class="inline">
									<?= Html::activeLabel($model, 'dob',
										['class' => 'my-2']) ?>
									<?= $form->field($model, 'dob')
									         ->textInput([
										         'data-flatpickr' => [
											         'datetimeFormat' => 'DD/MM/YYYY',
										         ],
									         ])->label(FALSE) ?>
								</li>
								<li class="inline">
									<button type="submit" class="btn btn-round btn-primary ms-auto">
										<?= Yii::t('common', 'Submit') ?>
									</button>
								</li>
							</ul>
						</div>
					</div>
					<?php ActiveForm::end(); ?>
				</div>
			</div>
		</div>
	</div>
</div>