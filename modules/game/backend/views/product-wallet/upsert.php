<?php
/**
 * @var \yii\web\View $this
 * @var \modules\game\models\ProductWallet $model
 */

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

?>

<?php $form = ActiveForm::begin([
	'id' => 'product-wallet'
]); ?>

<?= $form->field($model, 'name') ?>

<?= $form->field($model, 'code') ?>

<?= $form->field($model, 'rate') ?>

<?= $form->field($model, 'type_id')
         ->dropDownList($model->types,
	         ['data-toggle' => 'select', 'prompt' => Yii::t('game', 'All')]) ?>

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