<?php

/**
 * @var frontend\base\View $this
 * @var yii\bootstrap5\ActiveForm $form
 * @var \modules\wallet\frontend\models\Transfer $model
 */

use common\widgets\Alert;
use modules\customer\widgets\AccountHeader;
use modules\customer\widgets\Menu;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

$this->title = Yii::t('wallet', 'Transfer');
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
						<h1 class="heading text-large"><?= Yii::t('wallet', 'Transfer') ?></h1>
						<div class="row">
							<div class="col-lg-9 col-xl-8">
								<?= Alert::widget() ?>

								<?php $form = ActiveForm::begin([
									'id'          => 'transfer-form',
									'layout'      => ActiveForm::LAYOUT_HORIZONTAL,
									'fieldConfig' => [
										'horizontalCssClasses' => [
											'label'   => 'col-lg-3 mb-lg-0 mb-2 mt-2 text-lg-end',
											'wrapper' => 'col-lg-9'
										],
										'options'              => ['class' => 'mb-4 row']
									],
								]) ?>

								<div class="mb-4">
									<div class="row">
										<div class="col-lg-3 mb-lg-0 mb-2 mt-2 text-lg-end">
											<label for="wallet-from"><?= Yii::t('wallet',
													'Channel') ?></label>
										</div>

										<?= $form->field($model, 'from_wallet',
											['options' => ['class' => 'col-lg-4']])
										         ->begin() ?>
										<?= Html::activeDropDownList($model, 'from_wallet',
											$model->wallets, ['class' => 'form-select select2']) ?>
										<?= Html::error($model, 'from_wallet',
											['class' => 'invalid-feedback text-nowrap d-block']) ?>
										<?= $form->field($model, 'from_wallet')->end() ?>

										<div class="col-lg text-center">
											<i class="fas fa-long-arrow-alt-right fa-lg mt-3" aria-hidden="true"></i>
										</div>

										<?= $form->field($model, 'to_wallet',
											['options' => ['class' => 'col-lg-4']])
										         ->begin() ?>
										<?= Html::activeDropDownList($model, 'to_wallet',
											$model->wallets,
											['class' => 'form-select select2', 'id' => 'to-wallet']) ?>
										<?= Html::error($model, 'to_wallet',
											['class' => 'invalid-feedback text-nowrap d-block']) ?>
										<?= $form->field($model, 'to_wallet')->end() ?>
									</div>
								</div>

								<?= $form->field($model, 'amount')
								         ->input('text', ['class' => 'form-control numeric']) ?>

								<?= $form->field($model, 'promotion_id')
								         ->dropDownList(ArrayHelper::getColumn($model->promotions,
									         'label'),
									         [
										         'class'  => 'form-select select2',
										         'prompt' => Yii::t('wallet', 'Please select'),
										         'id'     => 'promotions'
									         ]) ?>

								<div class="row mb-4">
									<div class="col-lg-9 offset-lg-3">
										<button type="submit" class="btn btn-round btn-primary btn-wide"><?= Yii::t('wallet',
												'Submit') ?></button>
									</div>
								</div>

								<?php ActiveForm::end() ?>
							</div>
						</div>
						<div class="mb-4">
							<div class="dash-line"></div>
						</div>
						<div class="turnover">
							<div class="weekly mb-4">
								<div class="row">
									<div class="col-lg-4">
										<div class="box-round">
											<h4 class="heading text-normal fw-normal no-border text-white"><?= Yii::t('wallet',
													'Weekly Turnover Total') ?></h4>
											<div class="content inline align-items-center">
												<div class="number text-primary text-largest fw-bold"><?= Yii::$app->formatter->asCurrency($model->totalTurnovers) ?></div>
												<div class="action">
													<a href="javascript:" onclick="location.reload()" class="btn btn-outline-primary btn-sm"><i class="fas fa-redo" aria-hidden="true"></i></a>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="list">
								<div class="row">
									<?php foreach ($model->products as $product): ?><?php foreach ($product['products'] as $wallet): ?>
										<div class="col-xxl-3 col-xl-4 col-lg-6 mb-4">
											<div class="box-round">
												<div class="box-header inline">
													<div class="title"><?= Html::encode($wallet['name']) ?></div>
													<?= Html::a(Yii::t('wallet', 'All in'),
														['allin'],
														[
															'class' => 'btn btn-round btn-outline-primary btn-sm',
															'data'  => [
																'method'  => 'POST',
																'confirm' => Yii::t('wallet',
																	'Are you sure you want to all in to this channel?'),
																'params'  => ['id' => $wallet['code']]
															]
														]) ?>
												</div>
												<div class="box-body inline">
													<div class="currency"><?= $model->currency ?></div>
													<div class="number"><?= Yii::$app->formatter->asDecimal(floor($wallet['total'])) ?></div>
												</div>
												<div class="box-footer inline align-items-center">
													<div class="title"><?= Yii::t('wallet',
															'Weekly Turnover') ?></div>
													<?= Yii::$app->formatter->asCurrency($model->turnovers[$wallet['code']] ?? 0) ?>
												</div>
											</div>
										</div>
									<?php endforeach; ?><?php endforeach; ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

<?php
$promotions = Json::encode($model->playerPromotions);
$js         = <<<JS
	const player_promos = {$promotions};
	$('#promotions option:gt(0)').prop('disabled', true);
	$('#to-wallet').on('change', function(){
		$('#promotions option:gt(0)').prop('disabled', true); // disable all first
		
		let wallet_code = $(this).val();
		if(player_promos.hasOwnProperty(wallet_code)){
			let promotions = player_promos[wallet_code];
			$.each(promotions, function(index, value) {
				$('#promotions option').filter(function () {
					return $(this).val() == value;
				}).prop('disabled', false)
			})
		}
	});
	
	$('#promotions option:selected').prop('disabled', false); // disable all first
	
	$(document).on("input", ".numeric", function() {
	    this.value = this.value.replace(/\D/g,'');
	});
JS;

$this->registerJs($js);