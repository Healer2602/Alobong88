<?php

use common\models\Language;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var \modules\block\backend\models\BlockModel $model
 */

?>

<?php $form = ActiveForm::begin(); ?>


<?= $form->field($model, 'name')->textInput(['maxlength' => TRUE]) ?>

<?= $form->field($model, 'title')->textInput(['maxlength' => TRUE]) ?>

<?= $form->field($model, 'position')
         ->dropDownList($model->positions,
	         ['class' => 'custom-select', 'data-toggle' => 'select']) ?>

<?= $form->field($model, 'setting[css_class]')
         ->textInput(['maxlength' => TRUE])
         ->label('CSS Classes') ?>

<?= $form->field($model, 'language')
         ->dropDownList(Language::listLanguage(),
	         ['class' => 'custom-select', 'data-toggle' => 'select']) ?>

<?= $model->renderForm() ?>

	<div class="form-group mt-4">
		<?= Html::submitButton(Yii::t('common', 'Save'),
			['class' => 'btn btn-primary btn-block']) ?>

		<?= Html::a(Yii::t('common', 'Cancel'), ['index'],
			['class' => 'btn btn-link text-muted btn-block']) ?>
	</div>

<?php ActiveForm::end(); ?>