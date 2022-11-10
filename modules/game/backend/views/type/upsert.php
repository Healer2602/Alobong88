<?php
/**
 * @var \yii\web\View $this
 * @var \modules\game\models\GameType $model
 */

use modules\media\widgets\MediaInputModal;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

?>

<?php $form = ActiveForm::begin([
	'id' => 'game-type'
]); ?>

<?= $form->field($model, 'name') ?>

<?= $form->field($model, 'icon')
         ->widget(MediaInputModal::class, [
	         'current_path' => 'game',
	         'target'       => '#my-media-modal'
         ]) ?>

<?= $form->field($model, 'layout')
         ->dropDownList($model->layouts, ['data-toggle' => 'select']) ?>

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