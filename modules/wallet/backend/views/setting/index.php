<?php

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

/**
 * @var yii\web\View $this
 * @var \modules\wallet\models\Setting $model
 */

$this->title = Yii::t('wallet', 'eWallet Settings');

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('common', 'Settings'),
	'url'   => ['/setting/index']
];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $form = ActiveForm::begin() ?>
	<div class="row justify-content-around">
		<div class="col-md-6">
			<div class="m-portlet">
				<h2><?= Yii::t('wallet', 'Deposit') ?></h2>

				<?= $form->field($model, 'minimum_topup_first')->begin() ?>
				<?= Html::activeLabel($model, 'minimum_topup_first', ['class' => 'form-label']) ?>
				<div class="input-group input-group-merge">
					<?= Html::activeTextInput($model, 'minimum_topup_first',
						['class' => 'form-control form-control-prepended']) ?>

					<div class="input-group-text rounded-end">
						<span class="fe fe-dollar-sign"></span>
					</div>

					<?= Html::error($model, 'minimum_topup_first',
						['class' => 'invalid-feedback']) ?>
				</div>
				<?= $form->field($model, 'minimum_topup_first')->end() ?>

				<?= $form->field($model, 'minimum_topup')->begin() ?>
				<?= Html::activeLabel($model, 'minimum_topup', ['class' => 'form-label']) ?>
				<div class="input-group input-group-merge">
					<?= Html::activeTextInput($model, 'minimum_topup',
						['class' => 'form-control form-control-prepended']) ?>

					<div class="input-group-text rounded-end">
						<span class="fe fe-dollar-sign"></span>
					</div>

					<?= Html::error($model, 'minimum_topup', ['class' => 'invalid-feedback']) ?>
				</div>
				<?= $form->field($model, 'minimum_topup')->end() ?>

				<?= $form->field($model, 'maximum_topup')->begin() ?>
				<?= Html::activeLabel($model, 'maximum_topup', ['class' => 'form-label']) ?>
				<div class="input-group input-group-merge">
					<?= Html::activeTextInput($model, 'maximum_topup',
						['class' => 'form-control form-control-prepended']) ?>

					<div class="input-group-text rounded-end">
						<span class="fe fe-dollar-sign"></span>
					</div>

					<?= Html::error($model, 'maximum_topup', ['class' => 'invalid-feedback']) ?>
				</div>
				<?= $form->field($model, 'maximum_topup')->end() ?>

				<?= $form->field($model, 'topup_auto_reject')->begin() ?>
				<?= Html::activeLabel($model, 'topup_auto_reject', ['class' => 'form-label']) ?>
				<div class="input-group input-group-merge">
					<?= Html::activeTextInput($model, 'topup_auto_reject',
						['class' => 'form-control form-control-prepended']) ?>

					<div class="input-group-text rounded-end">
						h
					</div>

					<?= Html::error($model, 'topup_auto_reject', ['class' => 'invalid-feedback']) ?>
				</div>
				<?= $form->field($model, 'topup_auto_reject')->end() ?>

				<hr class="my-5">
				<h2><?= Yii::t('wallet', 'Withdraw') ?></h2>

				<?= $form->field($model, 'minimum_withdraw')->begin() ?>
				<?= Html::activeLabel($model, 'minimum_withdraw', ['class' => 'form-label']) ?>
				<div class="input-group input-group-merge">
					<?= Html::activeTextInput($model, 'minimum_withdraw',
						['class' => 'form-control form-control-prepended']) ?>

					<div class="input-group-text rounded-end">
						<span class="fe fe-dollar-sign"></span>
					</div>

					<?= Html::error($model, 'minimum_withdraw', ['class' => 'invalid-feedback']) ?>
				</div>
				<?= $form->field($model, 'minimum_withdraw')->end() ?>

				<?= $form->field($model, 'maximum_withdraw_wo_kyc')->begin() ?>
				<?= Html::activeLabel($model, 'maximum_withdraw_wo_kyc',
					['class' => 'form-label']) ?>
				<div class="input-group input-group-merge">
					<?= Html::activeTextInput($model, 'maximum_withdraw_wo_kyc',
						['class' => 'form-control form-control-prepended']) ?>

					<div class="input-group-text rounded-end">
						<span class="fe fe-dollar-sign"></span>
					</div>

					<?= Html::error($model, 'maximum_withdraw_wo_kyc',
						['class' => 'invalid-feedback']) ?>
				</div>
				<?= $form->field($model, 'maximum_withdraw_wo_kyc')->end() ?>

				<?= $form->field($model, 'maximum_withdraw')->begin() ?>
				<?= Html::activeLabel($model, 'maximum_withdraw', ['class' => 'form-label']) ?>
				<div class="input-group input-group-merge">
					<?= Html::activeTextInput($model, 'maximum_withdraw',
						['class' => 'form-control form-control-prepended']) ?>

					<div class="input-group-text rounded-end">
						<span class="fe fe-dollar-sign"></span>
					</div>

					<?= Html::error($model, 'maximum_withdraw', ['class' => 'invalid-feedback']) ?>
				</div>
				<?= $form->field($model, 'maximum_withdraw')->end() ?>

				<?= $form->field($model, 'withdraw_limit_balance')->begin() ?>
				<?= Html::activeLabel($model, 'withdraw_limit_balance',
					['class' => 'form-label']) ?>
				<div class="input-group input-group-merge">
					<?= Html::activeTextInput($model, 'withdraw_limit_balance',
						['class' => 'form-control form-control-prepended']) ?>

					<div class="input-group-text rounded-end">
						<span class="fe fe-dollar-sign"></span>
					</div>

					<?= Html::error($model, 'withdraw_limit_balance',
						['class' => 'invalid-feedback']) ?>
				</div>
				<?= $form->field($model, 'withdraw_limit_balance')->end() ?>

				<?= $form->field($model, 'daily_limit_balance')->begin() ?>
				<?= Html::activeLabel($model, 'daily_limit_balance', ['class' => 'form-label']) ?>
				<div class="input-group input-group-merge">
					<?= Html::activeTextInput($model, 'daily_limit_balance',
						['class' => 'form-control form-control-prepended']) ?>

					<div class="input-group-text rounded-end">
						<span class="fe fe-dollar-sign"></span>
					</div>

					<?= Html::error($model, 'daily_limit_balance',
						['class' => 'invalid-feedback']) ?>
				</div>
				<?= $form->field($model, 'daily_limit_balance')->end() ?>

				<?= $form->field($model, 'daily_count_balance')->textInput() ?>

				<div class="form-group">
					<?= Html::submitButton(Yii::t('common', 'Update'),
						['class' => 'btn btn-primary d-block w-100 mb-2']) ?>
					<?= Html::a(Yii::t('common', 'Cancel'), ['index'],
						['class' => 'btn text-muted d-block w-100']) ?>
				</div>
			</div>
		</div>
	</div>

<?php ActiveForm::end() ?>