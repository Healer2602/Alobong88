<?php
/**
 * @var \yii\web\View $this
 * @var \modules\wallet\backend\models\UpdateTransaction $model
 */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

?>

<?php $form = ActiveForm::begin([
	'id' => 'update-form'
]) ?>

<?= $form->field($model, 'receive_amount')
         ->textInput(['class' => 'form-control-plaintext', 'value' => $model->getDepositAmount()]) ?>

<?= $form->field($model, 'new_amount')
         ->textInput(['class' => 'form-control-plaintext', 'value' => Yii::$app->formatter->asCurrency($model->getReceiveAmount())]) ?>

<?= $form->field($model, 'confirm')->checkbox() ?>

<div class="mt-4">
	<?= Html::submitButton(Yii::t('common', 'Submit'),
		['class' => 'btn btn-primary w-100']) ?>

	<?= Html::a(Yii::t('common', 'Cancel'), '#',
		['class' => 'btn btn-link text-muted w-100', 'data-bs-dismiss' => 'modal']) ?>
</div>

<?php ActiveForm::end() ?>
