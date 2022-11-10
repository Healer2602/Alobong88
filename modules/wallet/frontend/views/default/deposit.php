<?php
/**
 * @var frontend\base\View $this
 * @var \modules\wallet\frontend\models\Deposit $model
 */

use common\widgets\Alert;
use modules\customer\widgets\AccountHeader;
use modules\customer\widgets\Menu;
use modules\themes\captain\AppAsset;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('wallet', 'Deposit');

$asset = AppAsset::register($this);
?>
<div class="default-bg">
	<div class="container">
		<?= AccountHeader::widget() ?>

		<div class="row">
			<div class="col-xxl-auto col-lg-3 mb-xxl-0 mb-4">
				<div class="menu-account">
					<?= Menu::widget() ?>
				</div>
			</div>
			<div class="col-xxl col-lg-9">
				<div class="main-form">
					<h1 class="heading text-large"><?= Yii::t('wallet', 'Deposit') ?></h1>

					<?= Alert::widget() ?>

					<div class="row">
						<div class="col-xxl-8">
							<!--Deposit Options-->
							<div class="mb-4">
								<div class="custom-options color">
									<div class="row align-items-center">
										<div class="col-lg-3 mb-lg-0 mb-2">
											<label class="fw-bold">
												<?= Yii::t('wallet', 'Deposit Options') ?>
											</label>
										</div>
										<div class="col-lg-9">
											<ul class="row row-cols-auto row-cols-xl-4 g-2 nav nav-pills" id="pills-tab" role="tablist">
												<?php
												foreach ($model->options as $key => $option):
													$is_active = $key == $model->opt;
													?>
													<li class="col nav-item <?= empty($option['total']) ? 'disabled' : '' ?>" role="presentation">
														<a href="<?= empty($option['total']) ? 'javascript:' : Url::to(['default/deposit', 'opt' => $key]) ?>" class="form-check deposit-option nav-link <?= $is_active ? 'active' : '' ?>" aria-selected="<?= $is_active ? 'true' : 'false' ?>">
															<label class="form-check-label deposit" for="<?= $key ?>">
																<?= Html::img($asset->baseUrl . '/img/icons/' . $key . '.png',
																	['alt' => '']); ?>

																<?= Html::encode($option['name']) ?>
															</label>
														</a>
													</li>
												<?php endforeach; ?>
											</ul>
										</div>
									</div>
								</div>
							</div>
							<!--Deposit Channel-->
							<div class="mb-4">
								<?= $this->render('_channels', ['model' => $model]) ?>
							</div>
						</div>

						<!--IMPORTANT NOTICE-->
						<div class="col-xxl-4">
							<div class="notice">
								<h2 class="heading text-large text-white"><?= Yii::t('wallet',
										'IMPORTANT NOTICE') ?></h2>
								<div class="content">
									<ul>
										<li><?= Yii::t('wallet',
												'Always check for the latest active deposit bank details before making a deposit') ?></li>
										<li><?= Yii::t('wallet',
												'For using deposit option "Bank Transfer", Please make the transfer before submit the transaction to avoid the transaction is delay.') ?></li>
										<li><?= Yii::t('wallet',
												'Depositor’s ACCOUNT NAME must match with registered full name. We do not encourage transaction made using 3rd party/company account.') ?></li>
										<li><?= Yii::t('wallet',
												'Please DO NOT fill "BK368" # or any sensitive words related to gambling as reference/remark in your online transfer transaction.') ?></li>
										<li><?= Yii::t('wallet',
												'Please take note that 1x turnover is required for all deposits made before any withdrawal can be processed.') ?></li>
										<li><?= Yii::t('wallet',
												'Kindly check with our 24/7 LIVECHAT if your transaction is pending for more than 5 minutes.') ?></li>
									</ul>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>