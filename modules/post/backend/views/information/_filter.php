<?php


/**
 * @var yii\web\View $this
 * @var array $filtering
 * @var array $filters
 */

use yii\bootstrap5\Html;

?>
<div class="filter">
	<form class="form-inline" action="" method="get">
		<div class="input-group">
			<label class="control-label"><?= Yii::t('common', 'Keywords') ?></label>
			<?= Html::textInput('s', $filtering['s'] ?? NULL,
				['class' => 'form-control', 'placeholder' => Yii::t('common', 'Keywords')]) ?>
		</div>
		<div class="input-group">
			<label class="control-label"><?= Yii::t('common', 'Language') ?></label>
			<?= Html::dropDownList('lang', $filtering['lang'] ?? NULL,
				$filters['langs'],
				['class' => 'form-select', 'data-toggle' => 'select', 'prompt' => Yii::t('common',
					'All')]) ?>
		</div>
		<div class="input-group">
			<label class="control-label"><?= Yii::t('post', 'Status') ?></label>
			<?= Html::dropDownList('state', $filtering['state'] ?? NULL,
				$filters['states'], ['class' => 'form-select', 'data-toggle' => 'select']) ?>
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