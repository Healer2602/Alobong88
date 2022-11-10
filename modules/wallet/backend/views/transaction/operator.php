<?php
/**
 * @var \yii\web\View $this
 * @var \modules\wallet\models\Transaction $model
 */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

?>

<?php $form = ActiveForm::begin([
	'id' => 'operator-form'
]) ?>

<?= $form->field($model, 'note')->textarea(['rows' => 5]) ?>

<div class="mt-4">
	<?= Html::submitButton(Yii::t('common', 'Submit'),
		['class' => 'btn btn-primary w-100']) ?>

	<?= Html::a(Yii::t('common', 'Cancel'), '#',
		['class' => 'btn btn-link text-muted w-100', 'data-bs-dismiss' => 'modal']) ?>
</div>

<?php ActiveForm::end() ?>
