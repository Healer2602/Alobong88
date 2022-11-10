<?php
/**
 * @var yii\web\View $this
 * @var \modules\matrix\models\Setting $model
 */

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

$this->title = Yii::t('matrix', 'API Settings');

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('common', 'Settings'),
	'url'   => ['/setting/index']
];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $form = ActiveForm::begin() ?>
	<div class="row justify-content-around">
		<div class="col-lg-7 col-xl-5">
			<h2 class="fw-bold mb-3 text-muted">Scheduler Settings</h2>
			<?= $form->field($model, 'interval_betlog') ?>
			<?= $form->field($model, 'interval_wallet') ?>

			<h2 class="fw-bold mb-3 mt-5 text-muted">API Parameters</h2>
			<?= $form->field($model, 'endpoint') ?>
			<?= $form->field($model, 'merchant_parent') ?>
			<?= $form->field($model, 'merchant_name') ?>
			<?= $form->field($model, 'merchant_code')->passwordInput() ?>
			<?= $form->field($model, 'merchant_prefix') ?>

			<div class="form-group">
				<?= Html::submitButton(Yii::t('common', 'Update'),
					['class' => 'btn btn-primary w-100 mb-2']) ?>
				<?= Html::a(Yii::t('common', 'Cancel'), ['index'],
					['class' => 'btn btn-link w-100']) ?>
			</div>
		</div>
	</div>

<?php ActiveForm::end() ?>