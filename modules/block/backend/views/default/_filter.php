<?php

/**
 * @var yii\web\View $this
 * @var \modules\block\backend\models\BlockSearch $searchModel
 */

use yii\bootstrap5\Html;

?>
<div class="filter">
	<form class="form-inline" action="" method="get">
		<div class="input-group">
			<label class="control-label"><?= Yii::t('common', 'Keywords') ?></label>
			<?= Html::textInput('keywords', $searchModel->keywords,
				['class' => 'form-control', 'placeholder' => Yii::t('block', 'Keywords')]) ?>
		</div>
		<div class="input-group">
			<label class="control-label"><?= Yii::t('block', 'Type') ?></label>
			<?= Html::dropDownList('type', $searchModel->type,
				$searchModel->types,
				['class' => 'form-select', 'data-toggle' => 'select', 'prompt' => Yii::t('common',
					'All')]) ?>
		</div>
		<div class="input-group">
			<label class="control-label"><?= Yii::t('block', 'Position') ?></label>
			<?= Html::dropDownList('position', $searchModel->position,
				$searchModel->positions,
				['class' => 'form-select', 'data-toggle' => 'select', 'prompt' => Yii::t('common',
					'All')]) ?>
		</div>
		<div class="input-group">
			<label class="control-label"><?= Yii::t('common', 'Language') ?></label>
			<?= Html::dropDownList('language', $searchModel->language,
				$searchModel->langs,
				['class' => 'form-select', 'data-toggle' => 'select', 'prompt' => Yii::t('common',
					'All')]) ?>
		</div>
		<div class="input-group">
			<label class="control-label"><?= Yii::t('common', 'Status') ?></label>
			<?= Html::dropDownList('status', $searchModel->status,
				$searchModel->statuses,
				['class' => 'form-select', 'data-toggle' => 'select']) ?>
		</div>
		<div class="input-group">
			<div class="input-group-btn">
				<button class="btn btn-outline-primary" type="submit">
					<i class="fe fe-search" aria-hidden="true"></i>
				</button>
				<button class="btn btn-outline-secondary clear" type="button">
					<i class="fe fe-x" aria-hidden="true"></i></button>
			</div>
		</div>
	</form>
</div>