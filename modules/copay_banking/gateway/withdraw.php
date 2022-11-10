<?php
/**
 * @var \yii\web\View $this
 * @var \modules\copay_banking\models\WithdrawForm $model
 * @var \modules\wallet\frontend\models\Withdraw $gateway
 * @var \yii\bootstrap5\ActiveForm $form
 */

use yii\bootstrap5\Html;
use yii\helpers\Json;
use yii\helpers\Url;

?>
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
						if ($model->banks):
							foreach ($model->banks as $bank): ?>

								<div class="col mb-2">
									<div class="form-check">
										<?= Html::activeRadio($model, 'bank_id', [
											'class'   => 'form-check-input',
											'value'   => $bank['id'],
											'id'      => 'bank-' . $bank['id'],
											'label'   => FALSE,
											'uncheck' => NULL,
											'checked' => $model->bank_id === $bank['id'],
											'data-id' => $bank['bank_id']
										]) ?>
										<label class="form-check-label" for="bank-<?= $bank['id'] ?>">
									<span class="image"><?= Html::img($bank['logo'],
											['alt' => $bank['name']]) ?></span>
											<span class="text-small"><?= $bank['name'] ?></span>
										</label>
									</div>
								</div>
							<?php endforeach;
						endif;
						?>

						<div class="col mb-2 add-bank">
							<div class="form-check h-100">
								<label class="form-check-label h-100 p-2 text-small" href="<?= Url::to(['default/add-bank']) ?>" data-bs-toggle="modal" data-bs-target="#modal-gateway-add-bank">
									<i class="fas fa-plus p-2" aria-hidden="true"></i> <?= Yii::t('common',
										'Add bank') ?>
								</label>
							</div>
						</div>
					</div>
					<?= Html::error($model, 'bank_id',
						['class' => 'invalid-feedback d-block']) ?>
				</div>
			</div>
		</div>
	</div>

	<div class="mb-4">
		<label class="fw-bold"><?= Yii::t('wallet', 'Transfer Information') ?></label>
	</div>

	<div class="mb-3">
		<?= $form->field($model, 'bank_account')
		         ->textInput(['id' => 'bank_account']) ?>
	</div>

	<div class="mb-3">
		<?= $form->field($model, 'account_number')
		         ->textInput(['id' => 'account_number']) ?>
	</div>

	<div class="mb-3">
		<?= $form->field($gateway, 'total')
		         ->textInput(['id' => 'withdraw-amount']) ?>

		<div class="row justify-content-end align-items-center">
			<div class="col-lg-9">
				<div class="d-flex justify-content-between my-2">
					<small class="text-primary">
						<i class="fas fa-exclamation-circle" aria-hidden="true"></i> <?= Html::encode($gateway->help) ?>
					</small>
				</div>
			</div>
		</div>
	</div>
	<div class="mb-4">
		<div class="dash-line"></div>
	</div>
	<div class="mb-3">
		<?= $form->field($model, 'bank_branch')->textInput(['id' => 'bank_branch']) ?>
	</div>
	<div class="mb-3">
		<?= $form->field($model, 'bank_province') ?>
	</div>
	<div class="mb-3">
		<?= $form->field($model, 'bank_city') ?>
	</div>
	<div class="mb-4">
		<div class="row">
			<div class="col-lg-9 offset-lg-3">
				<?= Html::submitButton(Yii::t('common', 'Submit'),
					['class' => 'btn btn-round btn-primary btn-wide']) ?>
			</div>
		</div>
	</div>

<?php
$accounts = Json::encode($model->accounts);
$js       = <<<JS
	var accounts = {$accounts};
	$('.bank-listing .form-check-input').click(function(){
		let value = $(this).data('id');
		if($(this).is(':checked')){
			bankAccount(value);
		}
	});
	
	bankAccount($('[name="WithdrawForm[bank_id]"]:checked').data('id'));
	
	function bankAccount(bank){
		$('#bank_account').val('');
		$('#account_number').val('');
		$('#bank_branch').val('');
		
		if(accounts.hasOwnProperty(bank)) { 
			let data = accounts[bank];
	
			if (data){
				$('#bank_account').val(data.account_name);
				$('#account_number').val(data.account_id);
				$('#bank_branch').val(data.account_branch);
			}
	   }
	}
JS;
$this->registerJs($js);