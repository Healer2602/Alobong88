<?php
/**
 * @var \yii\web\View $this
 * @var \modules\customer\models\Kyc $model
 */

use common\base\Status;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

?>

<?php $form = ActiveForm::begin(); ?>

<?= $form->field($model->customer, 'name')->textInput() ?>

<?= $form->field($model->customer, 'email')->textInput(['disabled' => TRUE]) ?>

<?= $form->field($model->customer, 'phone_number')->textInput(['disabled' => TRUE]) ?>

<?php if (!empty($model->front_image)): ?>
	<?= $form->beginField($model, 'front_image') ?>
	<?= Html::activeLabel($model, 'front_image', ['class' => 'd-block form-label']) ?>
	<img src="<?= $model->frontImage ?>" alt="..." class="img-fluid">
	<?= $form->endField() ?><?php endif; ?>

<?php if (!empty($model->back_image)): ?>
	<?= $form->beginField($model, 'back_image') ?>
	<?= Html::activeLabel($model, 'back_image',
		['class' => 'd-block form-label']) ?>
	<img src="<?= $model->backImage ?>" alt="..." class="img-fluid">
	<?= $form->endField() ?><?php endif; ?>

<?php if ($model->status == Status::STATUS_INACTIVE): ?>
	<div class="form-group mt-4 d-flex justify-content-between">
		<?= Html::a(Yii::t('customer', 'Approve'),
			['approve', 'id' => $model->id],
			['class'       => 'btn btn-success',
			 'data-method' => 'post']
		) ?>

		<?= Html::a(Yii::t('customer', 'Reject'),
			['reject', 'id' => $model->id],
			['class'          => 'btn btn-danger', 'data-bs-toggle' => "modal",
			 'data-bs-target' => "#global-modal",
			 'data-header'    => Yii::t('customer', 'Reject Customer eKYC {0}',
				 $model->customer->name)]
		) ?>
	</div>
<?php else: ?>
	<div class="form-group">
		<label class="mb-0"><?= $model->getAttributeLabel('status') ?></label>
		<div class="form-control-plaintext">
			<?= $model->statusHtml ?>
		</div>
	</div>
<?php endif; ?>

<?php ActiveForm::end(); ?>
