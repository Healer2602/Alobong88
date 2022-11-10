<?php
/**
 * @var \yii\web\View $this
 * @var \modules\game\backend\models\GameForm $model
 */

use modules\media\widgets\MediaInputModal;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

\modules\media\MediaAssets::register($this);
?>

<?php $form = ActiveForm::begin(['id' => 'game-form']); ?>
	<div class="card">
		<div class="card-header">
			<h4 class="card-header-title">
				<?= Yii::t('game', 'English') ?>
			</h4>
		</div>
		<div class="card-body">
			<?= $form->field($model, 'icon')
			         ->widget(MediaInputModal::class, [
				         'current_path' => 'game'
			         ]) ?>

			<?= $form->field($model, 'name') ?>
		</div>
	</div>

	<div class="card">
		<div class="card-header">
			<h4 class="card-header-title">
				<?= Yii::t('game', 'Chinese') ?>
			</h4>
		</div>
		<div class="card-body">
			<?= $form->field($model, "data_detail[" . $model::LANGUAGE_ZH . "][icon]")
			         ->widget(MediaInputModal::class, [
				         'current_path' => 'game',
			         ])->label(Yii::t('game', 'Icon')) ?>

			<?= $form->field($model, "data_detail[" . $model::LANGUAGE_ZH . "][name]")
			         ->label(Yii::t('game', 'Name')) ?>
		</div>
	</div>

	<div class="card">
		<div class="card-header">
			<h4 class="card-header-title">
				<?= Yii::t('game', 'Vietnamese') ?>
			</h4>
		</div>
		<div class="card-body">
			<?= $form->field($model, "data_detail[" . $model::LANGUAGE_VI . "][icon]")
			         ->widget(MediaInputModal::class, [
				         'current_path' => 'game',
			         ])->label(Yii::t('game', 'Icon')) ?>

			<?= $form->field($model, "data_detail[" . $model::LANGUAGE_VI . "][name]")
			         ->label(Yii::t('game', 'Name')) ?>
		</div>
	</div>

	<div class="card">
		<div class="card-body">
			<?= $form->field($model, 'code') ?>

			<?= $form->field($model, 'lines') ?>

			<?= $form->field($model, 'min_bet')->textInput(['type' => 'number']) ?>

			<?= $form->field($model, 'max_bet')->textInput(['type' => 'number']) ?>

			<?= $form->field($model, 'rtp') ?>

			<?= $form->field($model, 'vendor_id')
			         ->dropDownList($model->vendors,
				         ['data-toggle' => 'select', 'prompt' => Yii::t('game', 'Select')]) ?>

			<?= $form->field($model, 'type_id')
			         ->dropDownList($model->types,
				         ['data-toggle' => 'select', 'prompt' => Yii::t('game', 'Select')]) ?>

			<?= $form->field($model, 'feature')->checkbox() ?>

			<?= $form->field($model, 'free_to_play')->checkbox() ?>

			<?= $form->field($model, 'status')
			         ->dropDownList($model->statuses, ['data-toggle' => 'select']) ?>
		</div>
	</div>

	<div class="form-group mt-4">
		<?= Html::submitButton(Yii::t('common',
			$model->isNewRecord ? 'Create' : 'Update'),
			['class' => 'btn btn-primary w-100']) ?>

		<?= Html::a(Yii::t('common', 'Cancel'), ['index'],
			['class' => 'btn btn-link w-100 text-muted']) ?>
	</div>

<?php ActiveForm::end(); ?>