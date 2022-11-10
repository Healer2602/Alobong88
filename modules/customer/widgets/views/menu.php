<?php
/**
 * @var frontend\base\View $this
 * @var \modules\customer\models\Customer $user
 */

use modules\themes\captain\AppAsset;
use yii\bootstrap5\Nav;
use yii\helpers\Html;

$asset = AppAsset::register($this);
?>

<div class="menu-left">
	<div class="my-menu-item">
		<h2 class="heading text-large text-white"><?= Yii::t('customer', 'My Activities') ?></h2>

		<?= Nav::widget([
			'items'        => [
				[
					'label' => Html::img($asset->baseUrl . '/img/icons/icon-menu-deposit.png') . Yii::t('customer',
							'Deposit'),
					'url'   => ['/wallet/default/deposit'],
				],
				[
					'label' => Html::img($asset->baseUrl . '/img/icons/icon-menu-transfer.png') . Yii::t('customer',
							'Transfer'),
					'url'   => ['/wallet/transfer/index']
				],
				[
					'label' => Html::img($asset->baseUrl . '/img/icons/icon-menu-withdraw.png') . Yii::t('customer',
							'Withdraw'),
					'url'   => ['/wallet/default/withdraw'],
				],
				[
					'label'  => Html::img($asset->baseUrl . '/img/icons/icon-menu-history.png') . Yii::t('customer',
							'History'),
					'url'    => ['/wallet/history/index'],
					'active' => Yii::$app->controller->uniqueId === 'wallet/history'
				],
			],
			'options'      => [
				'class' => 'nav flex-lg-column'
			],
			'encodeLabels' => FALSE
		]); ?>
	</div>
	<div class="my-menu-item">
		<h2 class="heading text-large text-white"><?= Yii::t('customer', 'My Account') ?></h2>

		<?= Nav::widget([
			'items' => [
				[
					'label' => Html::img($asset->baseUrl . '/img/icons/icon-menu-info.png') . Yii::t('customer',
							'My Profile'),
					'url'   => ['/customer/default/index'],
				],
				[
					'label' => Html::img($asset->baseUrl . '/img/icons/icon-menu-info.png') . Yii::t('customer',
							'Account verification'),
					'url'   => ['/customer/default/kyc']
				],
				[
					'label' => Html::img($asset->baseUrl . '/img/icons/icon-menu-change-pass.png') . Yii::t('customer',
							'Change password'),
					'url'   => ['/customer/default/change-password'],
				],
				[
					'label' => Html::img($asset->baseUrl . '/img/icons/icon-menu-withdraw-details.png') . Yii::t('customer',
							'Withdrawal Details'),
					'url'   => ['/customer/default/withdraw-details'],
				],
			],
			'options'      => [
				'class' => 'nav flex-lg-column'
			],
			'encodeLabels' => FALSE
		]); ?>
	</div>
</div>