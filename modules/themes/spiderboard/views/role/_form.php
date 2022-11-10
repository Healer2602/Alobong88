<?php

use yii\bootstrap5\ActiveForm;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/** @var \backend\models\UserGroup $model */
/** @var \yii\web\View $this */
/** @var ArrayDataProvider $permissions */
?>

<div class="m-portlet">

	<?php $form = ActiveForm::begin([
		'id' => 'role_form'
	]); ?>

	<div class="row">
		<div class="col-lg-6">
			<?= $form->field($model, 'name') ?>
		</div>
	</div>

	<div class="card">
		<div class="permissions">
			<?= $this->render('_permissions', [
				'permissions'       => $permissions,
				'model'             => $model,
				'group_permissions' => ArrayHelper::getColumn($model->permissions,
					'user_permission_id'),
			]) ?>
		</div>

		<div class="card-footer">
			<?= Html::submitButton(Yii::t('common',
				$model->isNewRecord ? 'Create' : 'Update'),
				['class' => 'btn btn-primary']) ?>
			<?= Html::a(Yii::t('common', 'Cancel'), ['#'],
				['class' => 'btn btn-secondary ms-3', 'data-bs-dismiss' => "modal"]) ?>
		</div>
		<?php ActiveForm::end(); ?>
	</div>
</div>