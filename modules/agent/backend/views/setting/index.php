<?php

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

/**
 * @var yii\web\View $this
 * @var \modules\agent\models\Setting $model
 */

$this->title = Yii::t('wallet', 'Agent Settings');

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('common', 'Settings'),
	'url'   => ['/setting/index']
];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $form = ActiveForm::begin() ?>
	<div class="row justify-content-around">
		<div class="col-lg-6">
			<div class="m-portlet">
				<h3><?= Yii::t('agent', 'Commission Plans') ?></h3>

				<div class="row mb-4 align-items-center">
					<div class="col-2 fw-bold"><?= Yii::t('agent', 'Range 1') ?></div>
					<div class="col"><?= Html::activeTextInput($model, 'range_1[0]',
							['value' => $model->range_1[0] ?? NULL, 'class' => 'form-control']) ?></div>
					<div class="col"><?= Html::activeTextInput($model, 'range_1[1]',
							['value' => $model->range_1[1] ?? NULL, 'class' => 'form-control', 'id' => 'range-1-1']) ?></div>
				</div>

				<div class="row mb-4 align-items-center">
					<div class="col-2 fw-bold"><?= Yii::t('agent', 'Range 2') ?></div>
					<div class="col"><?= Html::activeTextInput($model, 'range_2[0]',
							['value' => $model->range_2[0] ?? NULL, 'class' => 'form-control', 'id' => 'range-2-0']) ?></div>
					<div class="col"><?= Html::activeTextInput($model, 'range_2[1]',
							['value' => $model->range_2[1] ?? NULL, 'class' => 'form-control', 'id' => 'range-2-1']) ?></div>
				</div>

				<div class="row mb-4 align-items-center">
					<div class="col-2 fw-bold"><?= Yii::t('agent', 'Range 3') ?></div>
					<div class="col"><?= Html::activeTextInput($model, 'range_3[0]',
							['value' => $model->range_3[0] ?? NULL, 'class' => 'form-control', 'id' => 'range-3-0']) ?></div>
					<div class="col"><?= Html::activeTextInput($model, 'range_3[1]',
							['value' => $model->range_3[1] ?? NULL, 'class' => 'form-control']) ?></div>
				</div>

				<div class="form-group">
					<?= Html::submitButton(Yii::t('common', 'Update'),
						['class' => 'btn btn-primary d-block w-100 mb-2']) ?>
					<?= Html::a(Yii::t('common', 'Cancel'), ['index'],
						['class' => 'btn text-muted d-block w-100']) ?>
				</div>
			</div>
		</div>
	</div>

<?php ActiveForm::end() ?>

<?php
$js = <<<JS
	$('#range-1-1').on('keyup', function(event){
		let value = $(this).val();
		$('#range-2-0').val(parseInt(value) + 1);
	});

	$('#range-2-1').on('keyup', function(event){
		let value = $(this).val();
		$('#range-3-0').val(parseInt(value) + 1);
	});
JS;

$this->registerJs($js);