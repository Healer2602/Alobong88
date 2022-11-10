<?php
/**
 * @var \yii\web\View $this
 * @var \modules\internet_banking\models\DepositForm $model
 * @var \modules\wallet\frontend\models\Deposit $gateway
 * @var \yii\bootstrap5\ActiveForm $form
 */

use yii\bootstrap5\Html;
use yii\helpers\Json;

?>

<?php if ($model->banks): ?>
	<div class="mb-4">
		<div class="custom-options bank-listing">
			<div class="row align-items-center">
				<label class="col-lg-3 mb-lg-0 mb-2">
					<?= $model->getAttributeLabel('bank_id') ?>
				</label>
				<div class="col-lg-9">
					<div class="row row-cols-md-2 row-cols-1 g-2">
						<?= Html::activeHiddenInput($model, 'bank_id', ['value' => 0]) ?>

						<?php foreach ($model->banks as $bank): ?>
							<div class="col mb-2">
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
					</div>
					<?= Html::error($model, 'bank_id', ['class' => 'invalid-feedback d-block']) ?>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>

	<div class="mb-4">
		<label class="fw-bold"><?= Yii::t('wallet', 'Transfer Information') ?></label>
	</div>

	<div class="mb-2">
		<?= $form->field($model, 'bank_account')
		         ->textInput(['class' => 'form-control icon-copy', 'id' => 'bank_account', 'readonly' => TRUE]) ?>
	</div>

	<div class="mb-4">
		<?= $form->field($model, 'account_number')
		         ->textInput(['class' => 'form-control icon-copy', 'id' => 'account_number', 'readonly' => TRUE])
		         ->hint(Yii::t('internet_banking', 'Branch: {0}',
			         "<span> $model->account_branch </span>"),
			         ['id' => 'account_branch']) ?>
	</div>

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
					<?php endif ?>
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
		<?= $form->field($model, 'deposit_channel')
		         ->dropDownList($model->channels,
			         ['class' => 'form-select select2']) ?>
	</div>

	<div class="mb-4">
		<?= $form->field($model, 'reference_id')->textInput() ?>
	</div>

	<div class="mb-4">
		<?= $form->beginField($model, 'receipt',
			['options' => ['class' => 'row form-group align-items-start']]) ?>
		<?= Html::activeLabel($model, 'receipt', ['class' => 'col-lg-3 mb-2 pt-1']) ?>
		<div class="col-lg-9 mb-2">
			<div class="child-form-group">
				<?= $form->field($model, 'receipt',
					['options' => ['class' => 'custom-file'], 'template' => '{input}{label}{error}'])
				         ->fileInput(['id' => 'receipt', 'class' => 'custom-file-input', 'accept' => '.png, .jpg, .jpeg'])
				         ->label(FALSE) ?>
				<div class="hint">
					<div class="content">
						<?= Yii::t('common',
							'<i class="fas fa-cloud-upload-alt"></i> Upload File') ?>
						<small>
							<?= Yii::t('common', 'Drag and drop or Browse your files') ?>
						</small>
					</div>
				</div>
				<div class="preview-image mt-2" data-label="<?= $model->getAttributeLabel('receipt') ?>">
					<?php if (!empty($model->receipt)): ?>
						<img src="<?= $model->receipt ?>" alt="...">
					<?php endif; ?>
				</div>
				<button type="button" class="remove btn btn-danger btn-sm <?= empty($model->receipt) ? 'd-none' : '' ?>">
					<i class="las la-trash" aria-hidden="true"></i></button>
			</div>
		</div>
		<?= $form->endField() ?>
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
	   if($(this).is(':checked')) { 
			let data = accounts[$(this).val()];
	
			if (data){
				$('#bank_account').val(data.account_name);
				$('#account_number').val(data.account_id);
				$('#account_branch span').html(data.account_branch);
			}
	   }
	});
	
	$('.child-form-group .btn.remove').on('click', function(){
	    $(this).parents('.child-form-group').find('.preview-image').html('');
	    $(this).parents('.child-form-group').find('.custom-file-input').val('');
	    $(this).addClass('d-none');
	});

	$(".custom-file-input").on('change', function () {
	    readURL(this);
	});
	
	function readURL(input) {
        let preview = $(input).parents('.form-group').find('.preview-image');
        preview.text('');

        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                let img = $('<img>').attr('src', e.target.result);
                preview.html(img);
                preview.siblings().removeClass('d-none');
				$('.field-receipt input').val(e.target.result);
            };

            reader.readAsDataURL(input.files[0]);
        }
    }
JS;
$this->registerJs($js);