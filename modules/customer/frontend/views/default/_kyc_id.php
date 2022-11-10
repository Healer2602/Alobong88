<?php

/**
 * @var frontend\base\View $this
 * @var yii\bootstrap5\ActiveForm $form
 * @var \modules\customer\frontend\models\KycForm $model
 */

use modules\customer\models\Kyc;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Alert;
use yii\helpers\Html;

?>

<?php if ($model->status == Kyc::STATUS_APPROVED):
	echo Alert::widget([
		'body'        => Html::tag('p',
				'<i class="las la-info la-3x"></i>',
				['class' => 'text-center mb-1']) . Yii::t('customer',
				'Your Citizen ID has been verified.'),
		'options'     => [
			'class' => 'alert-success text-center d-flex align-items-center'
		],
		'closeButton' => FALSE
	]);
elseif ($model->status == Kyc::STATUS_PENDING):
	echo Alert::widget([
		'body'        => Html::tag('p',
				'<i class="las la-exclamation-triangle la-3x"></i>',
				['class' => 'text-center mb-1']) . Yii::t('customer',
				'Your Citizen ID is awaiting moderation.'),
		'options'     => [
			'class' => 'alert-warning text-center d-flex align-items-center'
		],
		'closeButton' => FALSE
	]);
else: ?>
	<div class="mb-3">
		<h3 class="text-large text-white">
			<?= Yii::t('customer', 'Verify Your Citizen ID') ?>
		</h3>
		<small><?= Yii::t('customer',
				'Additional identification papers of the representative (ID card/Passport)') ?></small>
	</div>

	<?php
	if ($model->status == Kyc::STATUS_REJECTED){
		echo Alert::widget([
			'body'        => Html::tag('p',
					'<i class="las la-exclamation-triangle la-3x"></i>',
					['class' => 'text-center mb-1'])
			                 . Yii::t('customer',
					'Your Citizen ID has been rejected. Please update new information.')
			                 . Html::tag('div', '', ['class' => 'w-100'])
			                 . Html::tag('div',
					Yii::t('customer', 'Reason: {0}',
						[$model->reason]),
					['class' => 'mb-0 fw-bold']),
			'options'     => [
				'class' => 'alert-danger d-flex align-items-center flex-wrap'
			],
			'closeButton' => FALSE
		]);
	}
	?>

	<?php $form = ActiveForm::begin(['id' => 'kyc-form']); ?>
	<div class="row">
		<div class="col-lg-6 mb-4">
			<?= $form->beginField($model, 'front_image',
				['options' => ['class' => 'form-group align-items-start required']]) ?>
			<?= Html::activeLabel($model, 'front_image') ?>
			<div class="child-form-group">
				<?= $form->field($model, 'front_image',
					['options' => ['class' => 'custom-file'], 'template' => '{input}{label}{error}'])
				         ->fileInput(['id' => 'ekyc-front', 'class' => 'custom-file-input', 'accept' => '.png, .jpg, .jpeg'])
				         ->label(FALSE) ?>
				<div class="preview-image mt-2" data-label="<?= $model->getAttributeLabel('front_image') ?>">
					<?php if (!empty($model->front_image)): ?>
						<img src="<?= $model->front_image ?>" alt="...">
					<?php endif; ?>
				</div>
				<button type="button" class="remove btn btn-danger btn-sm <?= empty($model->front_image) ? 'd-none' : '' ?>">
					<i class="las la-trash" aria-hidden="true"></i>
				</button>
			</div>
			<?= $form->endField() ?>
		</div>
		<div class="col-lg-6 mb-4">
			<?= $form->beginField($model, 'back_image',
				['options' => ['class' => 'form-group align-items-start required']]) ?>
			<?= Html::activeLabel($model, 'back_image') ?>
			<div class="child-form-group">
				<?= $form->field($model, 'back_image',
					['options' => ['class' => 'custom-file'], 'template' => '{input}{label}{error}'])
				         ->fileInput(['id' => 'ekyc-back', 'class' => 'custom-file-input', 'accept' => '.png, .jpg, .jpeg'])
				         ->label(FALSE) ?>
				<div class="preview-image mt-2" data-label="<?= $model->getAttributeLabel('back_image') ?>">
					<?php if (!empty($model->back_image)): ?>
						<img src="<?= $model->back_image ?>" alt="...">
					<?php endif; ?>
				</div>
				<button type="button" class="remove btn btn-danger btn-sm <?= empty($model->front_image) ? 'd-none' : '' ?>">
					<i class="las la-trash" aria-hidden="true"></i>
				</button>
			</div>
			<?= $form->endField() ?>
		</div>

		<div>
			<?= Html::activeHiddenInput($model, 'verify') ?>
			<button type="submit" class="btn btn-round btn-primary btn-wide">
				<?= Yii::t('common', 'Submit') ?></button>
		</div>
	</div>
	<?php ActiveForm::end(); ?>

<?php endif ?>