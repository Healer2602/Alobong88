<?php

use common\models\Language;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/* @var yii\web\View $this */
/* @var modules\post\models\Category $model */
/* @var yii\bootstrap5\ActiveForm $form */
?>

	<div class="post-category-form">
		<div class="row justify-content-center">
			<div class="col-lg-7">
				<?php $form = ActiveForm::begin([
					'id' => 'category_form'
				]); ?>

				<div class="m-portlet">

					<?= $form->field($model, 'name')
					         ->textInput(['maxlength' => TRUE, 'class' => 'form-control form-control-lg']) ?>

					<?= $form->field($model, 'slug')->textInput(['maxlength' => TRUE]) ?>

					<?= $form->field($model, 'description')
					         ->textarea(['class' => 'form-control editor']) ?>

					<?= $form->field($model, 'language')
					         ->dropDownList(Language::listLanguage(),
						         ['class' => 'custom-select', 'data-toggle' => 'select']) ?>
				</div>

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
$this->registerJsVar('mediaModule', 'post');
