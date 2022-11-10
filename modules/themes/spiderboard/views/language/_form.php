<?php

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/**
 * @var \backend\base\View $this
 * @var \common\models\Language $model
 */
?>

<div class="row justify-content-center">

	<div class="col-md-6">
		<?php $form = ActiveForm::begin(); ?>

		<?= $form->field($model, 'name') ?>

		<?= $form->field($model, 'key')
		         ->dropDownList($model->languages,
			         ['class' => 'custom-select', 'data-toggle' => 'select']) ?>

		<?= $form->field($model, 'is_default')
		         ->checkbox(['disabled' => !empty($model->is_default)]) ?>

		<?= $form->field($model, 'status')
		         ->dropDownList($model->statuses,
			         ['class' => 'custom-select', 'data-toggle' => 'select', 'disabled' => !empty($model->is_default)]) ?>

		<div class="form-group">
			<?= Html::submitButton(Yii::t('common', $model->isNewRecord ? 'Create' : 'Update'),
				['class' => 'btn btn-primary d-block w-100']) ?>

			<?= Html::a(Yii::t('common', 'Cancel'), ['index'],
				['class' => 'btn btn-link d-block text-muted w-100']) ?>
		</div>

		<?php ActiveForm::end(); ?>
	</div>

</div>
