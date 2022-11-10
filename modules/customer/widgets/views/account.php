<?php
/**
 * @var yii\web\View $this
 * @var \modules\customer\frontend\models\CustomerIdentity $model
 */

use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\helpers\Url;

?>

<div class="information">
	<div class="account-info d-lg-block d-none">
		<ul class="nav top-info">
			<li class="nav-item">
				<div class="nav-link">
					<div class="rank small <?= $model->rank->type ?? '' ?>">
						<div class="image"></div>
						<div class="content text-white fs-6">
							<?= Html::encode($model->rank->name ?? '') ?>
						</div>
					</div>
				</div>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="<?= Url::to(['/customer/default/index']) ?>">
					<?= Yii::t('customer', 'Welcome: {0}',
						Html::tag('span', $model->shortName,
							['class' => 'text-primary'])) ?>
				</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="<?= Url::to(['/customer/default/index']) ?>"><?= $model->currency ?>:
					<span class="text-primary"><?= Yii::$app->formatter->asDecimal(floor($model->wallet->balance ?? 0)) ?></span></a>
			</li>
		</ul>
		<?= Nav::widget([
			'items' => [
				[
					'label' => Yii::t('customer', 'Deposit'),
					'url'   => ['/wallet/default/deposit'],
				],
				[
					'label' => Yii::t('customer', 'Transfer'),
					'url'   => ['/wallet/transfer/index']
				],
				[
					'label' => Yii::t('customer', 'Withdraw'),
					'url'   => ['/wallet/default/withdraw'],
				],
				[
					'label' => Yii::t('customer', 'History'),
					'url'   => ['/wallet/history/index'],
				],
				[
					'label' => Yii::t('customer', 'Logout'),
					'url'   => ['/customer/default/logout'],
				],
			],
		]); ?>

	</div>
	<div class="d-block d-lg-none">
		<div class="dropdown">
			<a class="dropdown-toggle mobile-account text-nowrap" href="#" role="button" id="user-dropdown-menu" data-bs-toggle="dropdown" aria-expanded="false">
				<div class="rank small <?= $model->rank->type ?? '' ?>">
					<div class="content text-white fs-6">
						<?= Html::encode($model->shortName) ?>
					</div>
				</div>
			</a>
			<ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdown-menu">
				<li>
					<a class="dropdown-item" href="<?= Url::to(['/wallet/deposit']) ?>"><?= Yii::t('customer',
							'My Account') ?></a>
				</li>
				<li>
					<a class="dropdown-item" href="<?= Url::to(['/customer/default/logout']) ?>"><?= Yii::t('customer',
							'Logout') ?></a>
				</li>
			</ul>
		</div>
	</div>
</div>

