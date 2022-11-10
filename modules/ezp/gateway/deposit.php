<?php
/**
 * @var \yii\web\View $this
 * @var \modules\ezp\models\DepositForm $model
 * @var \modules\wallet\frontend\models\Deposit $gateway
 * @var \yii\bootstrap5\ActiveForm $form
 */

use yii\bootstrap5\Html;
use yii\helpers\ArrayHelper;

?>
<?php if ($model->banks): ?>
	<div class="mb-4">
		<div class="custom-options bank-listing">
			<div class="row align-items-center">
				<label class="col-lg-3 mb-lg-0 mb-2">
					<?= $model->getAttributeLabel('bank_id') ?>
				</label>
				<div class="col-lg-9">
					<div class="row row-cols-md-2 row-cols-1 g-2 list-show">
						<?= Html::activeHiddenInput($model, 'bank_id', ['value' => 0]) ?>

						<?php
						$total_bank = 0;
						foreach ($model->banks as $bank):
							if ($gateway->opt != 'usdt' && ArrayHelper::isIn($bank['code'],
									$model::UST)){
								continue;
							}elseif ($gateway->opt == 'usdt' && !ArrayHelper::isIn($bank['code'],
									$model::UST)){
								continue;
							}
							$total_bank ++;
							?>
							<div class="col mb-2 item">
								<div class="form-check">
									<?= Html::activeRadio($model, 'bank_id', [
										'class'   => 'form-check-input',
										'value'   => $bank['id'],
										'id'      => 'bank-' . $bank['id'],
										'label'   => FALSE,
										'uncheck' => NULL,
										'checked' => $model->bank_id === $bank['id']
									]) ?>
									<label class="form-check-label" for="bank-<?= $bank['id'] ?>">
									<span class="image"><?= Html::img($bank['logo'],
											['alt' => $bank['name']]) ?></span>
										<span class="text-small"><?= $bank['name'] ?></span>
									</label>
								</div>
							</div>
						<?php endforeach; ?>

						<?php if ($total_bank > 5) : ?>
							<div class="col mb-2 more-bank">
								<div class="form-check h-100">
									<label class="form-check-label h-100 p-2 text-small">
										<i class="fas fa-plus p-2" aria-hidden="true"></i> <?= Yii::t('common',
											'More banks') ?>
									</label>
								</div>
							</div>
						<?php endif; ?>
					</div>
					<?= Html::error($model, 'bank_id',
						['class' => 'invalid-feedback d-block']) ?>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>

<div class="mb-4">
	<div class="dash-line"></div>
</div>

<div class="mb-4">
	<?= $form->field($gateway, 'total')
	         ->textInput(['id' => 'deposit-amount']) ?>

	<div class="row justify-content-end align-items-center">
		<div class="col-lg-9">
			<div class="d-flex justify-content-between my-2">
				<?php if ($gateway->rate > 1): ?>
					<small class="text-white">
						1 = 1000
					</small>
				<?php endif; ?>
				<small class="text-primary">
					<i class="fas fa-exclamation-circle" aria-hidden="true"></i> <?= Html::encode($gateway->help) ?>
				</small>
			</div>
		</div>
		<div class="col-lg-9">
			<div class="quick-chose btn-group w-100" role="group">
				<?php
				$quick_options = [250, 500, 1600, 2700, 5500];
				foreach ($quick_options as $quick_option){
					$content = Html::hiddenInput('quick_options',
							$quick_option) . $quick_option;
					echo Html::label($content, '',
						['type' => 'button', 'class' => 'btn btn-info']);
				}
				?>
			</div>
		</div>
	</div>
</div>
<div class="mb-4">
	<div class="row">
		<div class="col-lg-9 offset-lg-3">
			<?= Html::submitButton(Yii::t('common', 'Submit'),
				['class' => 'btn btn-round btn-primary btn-wide']) ?>
		</div>
	</div>
</div>