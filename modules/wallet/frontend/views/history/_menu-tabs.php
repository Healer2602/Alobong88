<?php
/**
 * @var frontend\base\View $this
 */

use yii\bootstrap5\Nav;

$items = [
	[
		'label' => Yii::t('wallet', 'Betting Summary'),
		'url'   => ['/wallet/history/index']
	],
	[
		'label' => Yii::t('wallet', 'Transfer'),
		'url'   => ['/wallet/history/transfer']
	],
	[
		'label' => Yii::t('wallet', 'Withdrawal/Deposit'),
		'url'   => ['/wallet/history/log']
	],
	[
		'label'   => Yii::t('wallet', 'Rebate/Cashback'),
		'url'     => ['#'],
		'options' => ['class' => 'd-none']
	],
];
?>
	<button class="btn btn-round btn-outline-secondary btn-filter">
		<i class="fas fa-filter" aria-hidden="true"></i><?= Yii::t('wallet', 'Filter') ?>
	</button>

<?= Nav::widget([
	'items'   => $items,
	'options' => ['class' => 'nav nav-tabs']
]) ?>