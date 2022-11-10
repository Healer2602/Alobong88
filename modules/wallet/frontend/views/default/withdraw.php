<?php
/**
 * @var frontend\base\View $this
 * @var \modules\wallet\frontend\models\Withdraw $model
 */

use common\widgets\Alert;
use modules\customer\widgets\AccountHeader;
use modules\customer\widgets\Menu;
use modules\themes\captain\AppAsset;
use modules\wallet\assets\Assets;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Modal;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

$this->title = Yii::t('wallet', 'Withdraw');

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
						<h1 class="heading text-large"><?= Yii::t('wallet', 'Withdraw') ?></h1>

						<?= Alert::widget() ?>

						<?php if ($model->needKyc): ?>
							<div class="alert alert-warning my-3" role="alert">
								<div class="row align-items-center">
									<div class="col">
										<?= Yii::t('wallet',
											'You must complete account verification to be able to withdraw money') ?>
									</div>
									<div class="col-auto">
										<?= Html::a(Yii::t('wallet', 'KYC Now'),
											['/customer/default/kyc'],
											['class' => 'btn btn-warning']) ?>
									</div>
								</div>
							</div>
						<?php elseif ($turnoverMessage = $model->turnoverMessage): ?>
							<div class="alert alert-danger my-3" role="alert">
								<?= Html::encode($turnoverMessage) ?>
							</div>
						<?php endif ?>

						<div class="row">
							<div class="col-xxl-8">
								<!--withdraw Options-->
								<div class="mb-4">
									<div class="custom-options color">
										<div class="row align-items-center">
											<div class="col-lg-3 mb-lg-0 mb-2">
												<label class="fw-bold">
													<?= Yii::t('wallet', 'Withdraw Options') ?>
												</label>
											</div>
											<div class="col-lg-9">
												<ul class="row row-cols-auto row-cols-xl-4 g-2 nav nav-pills" id="pills-tab" role="tablist">
													<?php
													foreach ($model->options as $key => $option):
														$is_active = $key == $model->opt;
														?>
														<li class="col nav-item <?= empty($option['total']) ? 'disabled' : '' ?>" role="presentation">
															<a href="<?= empty($option['total']) ? 'javascript:' : Url::to(['default/withdraw', 'opt' => $key]) ?>" class="form-check withdraw-option nav-link <?= $is_active ? 'active' : '' ?>" aria-selected="<?= $is_active ? 'true' : 'false' ?>">
																<label class="form-check-label withdraw" for="<?= $key ?>">
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
								<!--withdraw Channel-->
								<div class="mb-4">

									<?php
									$form = ActiveForm::begin([
										'id'          => 'bank-transfer',
										'layout'      => ActiveForm::LAYOUT_HORIZONTAL,
										'fieldConfig' => [
											'horizontalCssClasses' => [
												'label'   => 'col-lg-3 mb-2 pt-1',
												'wrapper' => 'col-lg-9 mb-2',
											],
											'options'              => ['class' => 'row']
										],
										'action'      => ['/wallet/default/withdraw', 'opt' => $model->opt]
									]);
									?>

									<?php if (count($model->channels) > 1): ?>
										<div class="custom-options color mb-4">
											<div class="row align-items-center">
												<div class="col-lg-3 mb-lg-0 mb-2">
													<?= Html::label(Yii::t('wallet',
														'Withdraw Channel')) ?>
												</div>
												<div class="col-lg-9">
													<div class="row row-cols-3 row-cols-md-4 g-2">
														<?php foreach ($model->channels as $channel): ?>
															<div class="col">
																<div class="form-check withdraw-channel">
																	<?= Html::activeRadio($model,
																		'gateway', [
																			'class'   => 'form-check-input',
																			'value'   => $channel['key'],
																			'id'      => 'gateway-' . $channel['key'],
																			'label'   => FALSE,
																			'uncheck' => NULL,
																			'checked' => $model->gateway === $channel['key']
																		]) ?>
																	<label class="form-check-label channel" for="gateway-<?= $channel['key'] ?>">
																		<?php
																		echo Html::img($channel['icon']);
																		echo Yii::t('wallet',
																			$channel['title']);
																		?>
																	</label>
																</div>
															</div>
														<?php endforeach; ?>
													</div>
												</div>
											</div>
										</div>
									<?php else: ?>
										<div class="withdraw-channel d-none">
											<?= Html::activeRadio($model, 'gateway',
												['checked' => TRUE, 'value' => $model->channels[0]['key'] ?? NULL]) ?>
										</div>
									<?php endif; ?>

									<div class="form-withdraw">
										<?php
										Pjax::begin([
											'id'                 => 'pjax-withdraw-form',
											'enablePushState'    => FALSE,
											'enableReplaceState' => FALSE,
											'timeout'            => FALSE
										]);

										if (!empty($model->gatewayModel->formPath)){
											echo $this->render($model->gatewayModel->formPath, [
												'model'   => $model->model,
												'gateway' => $model,
												'form'    => $form
											]);
										}

										Pjax::end();
										?>
									</div>

									<?php ActiveForm::end(); ?>
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
													'Kindly check with our 24/7 LIVECHAT if your transaction is pending for more than 10 minutes.') ?></li>
											<li><?= Yii::t('wallet',
													'Withdrawal bank account name must match with registered full name, member is not allow withdrawal to 3rd party bank account.') ?></li>
											<li><?= Yii::t('wallet',
													'Some game provider requires 15 till 30 minutes of report sync time, kindly bear with us during the required sync time.') ?></li>
											<li><?= Yii::t('wallet',
													'Please make sure your turnover requirement has been achieved before making a withdrawal transaction to avoid inconvenience.') ?></li>
											<li><?= Yii::t('wallet',
													'If there is any discrepancy or you may have any other further withdrawal inquiries, kindly contact our 24/7 LIVECHAT. Thank you.') ?></li>
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
<?php
Modal::begin([
	'id'      => 'modal-gateway-add-bank',
	'title'   => Yii::t('wallet', 'Add Bank'),
	'options' => ['class' => 'modal-ajax fade media-dialog', 'tabindex' => NULL],
	'size'    => 'modal-lg'
]);

Modal::end();
Assets::register($this);
$withdraw_url = Url::to(['default/withdraw-form', 'opt' => $model->opt]);
$js           = <<<JS
	$(".withdraw-channel").click(function(e) {
		e.preventDefault();
		let channel = $(this).find(':input[type=radio]');
		channel.prop('checked',true);
		
		$.pjax.reload({
		    container:"#pjax-withdraw-form",
		    data: {id:channel.val()},
		    url:  "{$withdraw_url}",
		    push: false,
		    replace: false,
		    timeout: false,
		    type: "POST"
		});
		
		e.stopPropagation();
		e.stopImmediatePropagation();
	});
JS;
$this->registerJs($js);