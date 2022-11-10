<?php

use yii\bootstrap5\Html;

/**
 * @var yii\web\View $this
 * @var array $filtering
 * @var array $filters
 */
?>

<form class="form-inline" action="" method="get">
	<div class="input-group">
		<label class="control-label"><?= Yii::t('common', 'Keywords') ?></label>
		<?= Html::textInput('s', $filtering['s'] ?? NULL,
			['class' => 'form-control', 'placeholder' => Yii::t('common', 'Keywords')]) ?>
	</div>
	<?php if (!empty($filters['wallets'])): ?>
		<div class="input-group">
			<label class="control-label"><?= Yii::t('wallet',
					'Wallet') ?></label>
			<?= Html::dropDownList('wallet', $filtering['wallet'] ?? NULL,
				$filters['wallets'], [
					'class'       => 'form-select',
					'data-toggle' => 'select'
				]) ?>
		</div>
	<?php endif; ?>
	<?php if (!empty($filters['types'])): ?>
		<div class="input-group">
			<label class="control-label"><?= Yii::t('wallet',
					'Transaction Type') ?></label>
			<?= Html::dropDownList('type', $filtering['type'] ?? NULL,
				$filters['types'], [
					'class'       => 'form-select',
					'data-toggle' => 'select',
					'prompt'      => Yii::t('common', 'All')
				]) ?>
		</div>
	<?php endif; ?>
	<div class="input-group">
		<label class="control-label"><?= Yii::t('wallet', 'Player') ?></label>
		<?= Html::dropDownList('customer', $filtering['customer'] ?? NULL,
			$filters['customers'], [
				'class'       => 'form-select',
				'data-toggle' => 'select',
				'prompt'      => Yii::t('common', 'All')
			]) ?>
	</div>
	<div class="input-group">
		<label class="control-label"><?= Yii::t('wallet', 'Transaction Date') ?></label>
		<?= Html::textInput('date_range', $filtering['date_range'] ?? NULL,
			[
				'class'          => 'form-control',
				'data-flatpickr' => '{"mode": "range"}'
			]) ?>
	</div>
	<?php if (!empty($filters['states'])): ?>
		<div class="input-group">
			<label class="control-label"><?= Yii::t('common', 'Status') ?></label>
			<?= Html::dropDownList('state', $filtering['state'] ?? NULL,
				$filters['states'], [
					'class'       => 'form-select',
					'data-toggle' => 'select',
					'prompt'      => Yii::t('common', 'All')
				]) ?>
		</div>
	<?php endif; ?>
	<div class="input-group">
		<div class="input-group-btn">
			<button class="btn btn-outline-primary" type="submit">
				<i class="fe fe-search" aria-hidden="true"></i>
			</button>
			<button class="btn btn-outline-secondary clear" type="button">
				<i class="fe fe-x" aria-hidden="true"></i>
			</button>
		</div>
	</div>
</form>
