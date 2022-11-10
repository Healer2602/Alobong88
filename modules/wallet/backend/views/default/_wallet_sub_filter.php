<?php
/**
 * @var yii\web\View $this
 * @var array $filtering
 * @var array $filters
 */

use modules\wallet\assets\AdminAssets;
use modules\wallet\models\WalletSub;
use yii\bootstrap5\Html;

AdminAssets::register($this);
?>

<div class="filter wallet-filter">
	<form class="form-inline" action="" method="get">
		<div class="input-group">
			<label class="control-label"><?= Yii::t('common', 'Keywords') ?></label>
			<?= Html::textInput('keywords', $filtering['keywords'] ?? NULL,
				['class' => 'form-control', 'placeholder' => Yii::t('common', 'Keywords')]) ?>
		</div>
		<div class="input-group">
			<label class="control-label"><?= Yii::t('common', 'Status') ?></label>
			<?= Html::dropDownList('ws_state', $filtering['ws_state'] ?? NULL,
				WalletSub::statuses(), [
					'class'       => 'form-select',
					'data-toggle' => 'select',
					'prompt'      => Yii::t('common', 'All')
				]) ?>
		</div>
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
</div>