<?php
/**
 * @var \yii\web\View $this
 * @var \modules\game\models\BetlogProvider $model
 */

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

?>

<?php $form = ActiveForm::begin([
	'id' => 'betlog-provider'
]); ?>

<?= $form->field($model, 'product_wallet') ?>

<?= $form->field($model, 'code') ?>

<?= $form->field($model, 'vendor_id')
         ->dropDownList($model->vendors, ['data-toggle' => 'select']) ?>

<?= $form->field($model, 'status')
         ->dropDownList($model->statuses, ['data-toggle' => 'select']) ?>

<div class="form-group mt-4">
	<?= Html::submitButton(Yii::t('common',
		$model->isNewRecord ? 'Create' : 'Update'),
		['class' => 'btn btn-primary w-100']) ?>

	<?= Html::a(Yii::t('common', 'Cancel'), ['index'],
		['class' => 'btn btn-link w-100 text-muted']) ?>
</div>

<?php ActiveForm::end(); ?>
