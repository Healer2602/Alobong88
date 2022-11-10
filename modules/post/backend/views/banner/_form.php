<?php
/**
 * @var yii\web\View $this
 * @var \modules\post\backend\models\Banner $model
 * @var yii\bootstrap5\ActiveForm $form
 */

use common\models\Language;
use modules\media\widgets\MediaInputModal;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

?>

	<div class="banner-form">
		<div class="row justify-content-center">
			<div class="col-lg-7">
				<?php $form = ActiveForm::begin(); ?>

				<?= $form->field($model, 'name')
				         ->textInput(['maxlength' => TRUE, 'class' => 'form-control form-control-lg']) ?>

				<?= $form->field($model, 'thumbnail')
				         ->widget(MediaInputModal::class, [
					         'current_path' => $model->mediaPath,
				         ]) ?>

				<?= $form->field($model, 'intro')
				         ->widget(MediaInputModal::class, [
					         'current_path' => $model->mediaPath,
				         ])->label('Banner Image Mobile') ?>

				<?= $form->field($model, 'content[url]')
				         ->textInput(['maxlength' => TRUE])->label(Yii::t('post', 'URL')) ?>

				<?= $form->field($model, 'content[target]')
				         ->dropDownList($model->targets,
					         ['class' => 'custom-select', 'data-toggle' => 'select'])
				         ->label(Yii::t('post', 'URL Target')) ?>

				<?= $form->field($model, 'position')
				         ->dropDownList($model->positions,
					         ['class' => 'custom-select', 'data-toggle' => 'select'])
				         ->label(Yii::t('post', 'Position')) ?>

				<?= $form->field($model, 'language')
				         ->dropDownList(Language::listLanguage(),
					         ['class' => 'custom-select', 'data-toggle' => 'select']) ?>

				<?= $form->field($model, 'status')
				         ->dropDownList($model->statuses,
					         ['class' => 'custom-select', 'data-toggle' => 'select']) ?>

				<div class="form-group mt-4">
					<?= Html::submitButton(Yii::t('common',
						$model->isNewRecord ? 'Create' : 'Update'),
						['class' => 'btn btn-primary d-block w-100']) ?>

					<?= Html::a(Yii::t('common', 'Cancel'), ['index'],
						['class' => 'btn btn-link text-muted d-block w-100']) ?>
				</div>

				<?php ActiveForm::end(); ?>

			</div>
		</div>
	</div>

<?php
$this->registerJsVar('mediaModule', 'banner');