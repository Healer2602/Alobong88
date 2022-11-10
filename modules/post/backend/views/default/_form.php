<?php
/**
 * @var yii\web\View $this
 * @var \modules\post\models\Post $model
 * @var yii\bootstrap5\ActiveForm $form
 */

use common\models\Language;
use modules\media\widgets\MediaInputModal;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

?>

	<div class="post-form">

		<?php $form = ActiveForm::begin(); ?>

		<div class="row">
			<div class="col-md-8">
				<div class="m-portlet">
					<?= $form->field($model, 'name')
					         ->textInput(['maxlength' => TRUE, 'class' => 'form-control form-control-lg']) ?>

					<?= $form->field($model, 'slug')->textInput(['maxlength' => TRUE]) ?>

					<?= $form->field($model, 'intro')->textarea(['rows' => 6]) ?>

					<?= $form->field($model, 'content')
					         ->textarea(['class' => 'form-control editor']) ?>

				</div>
			</div>
			<div class="col-md-4">
				<div class="m-portlet">
					<?= $form->field($model, 'category_id')
					         ->dropDownList($model->categories,
						         ['class' => 'custom-select', 'data-toggle' => 'select', 'prompt' => Yii::t('post',
							         'Select a category')]) ?>

					<?= $form->field($model, 'thumbnail')
					         ->widget(MediaInputModal::class, [
						         'current_path' => $model->mediaPath
					         ]) ?>

					<?= $form->field($model, 'language')
					         ->dropDownList(Language::listLanguage(),
						         ['class' => 'custom-select', 'data-toggle' => 'select']) ?>

					<?= $form->field($model, 'tags')
					         ->dropDownList($model->tags ?: [],
						         ['data-toggle' => 'tags', 'multiple' => TRUE]) ?>

					<?= $form->field($model, 'related_tags')
					         ->dropDownList($model->related_tags ?: [],
						         ['data-toggle' => 'tags', 'multiple' => TRUE]) ?>

					<?= $form->field($model, 'status')->dropDownList($model->statuses) ?>

					<div class="form-group mt-4">
						<?= Html::submitButton(Yii::t('common',
							$model->isNewRecord ? 'Create' : 'Update'),
							['class' => 'btn btn-primary d-block w-100']) ?>

						<?= Html::a(Yii::t('common', 'Cancel'), ['index'],
							['class' => 'btn btn-link text-muted d-block w-100']) ?>
					</div>
				</div>
			</div>
		</div>

		<?php ActiveForm::end(); ?>

	</div>

<?php
$this->registerJsVar('mediaModule', 'post');