<?php
/**
 * @var \yii\web\View $this
 * @var \modules\wallet\models\Transaction $model
 */

use yii\bootstrap5\Html;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\web\View;

?>
	<div class="list-group list-group-flush mt-n3 mb-3">
		<div class="list-group-item">
			<div class="row align-items-center">
				<div class="col fw-bold">
					<?= $model->getAttributeLabel('transaction_id') ?>
				</div>
				<div class="col-auto">
					<?= $model->transaction_id ?>
				</div>
			</div>
		</div>
		<div class="list-group-item">
			<div class="row align-items-center">
				<div class="col fw-bold">
					<?= $model->getAttributeLabel('created_at') ?>
				</div>
				<div class="col-auto">
					<?= Yii::$app->formatter->asDatetime($model->created_at) ?>
				</div>
			</div>
		</div>
		<div class="list-group-item">
			<div class="row align-items-center">
				<div class="col fw-bold">
					<?= $model->getAttributeLabel('customer.name') ?>
				</div>
				<div class="col-auto">
					<?= Html::encode($model->customerName) ?>
				</div>
			</div>
		</div>
		<div class="list-group-item">
			<div class="row align-items-center">
				<div class="col fw-bold">
					<?= $model->getAttributeLabel('type') ?>
				</div>
				<div class="col-auto">
					<?= $model->typeHtml ?>
				</div>
			</div>
		</div>
		<div class="list-group-item">
			<div class="row align-items-center">
				<div class="col fw-bold">
					<?= $model->getAttributeLabel('status') ?>
				</div>
				<div class="col-auto">
					<?= $model->statusHtml ?>
				</div>
			</div>
		</div>
		<div class="list-group-item">
			<div class="row align-items-center">
				<div class="col fw-bold">
					<?= $model->getAttributeLabel('amount') ?>
				</div>
				<div class="col-auto">
					<?= Yii::$app->formatter->asCurrency($model->amount) ?>
				</div>
			</div>
		</div>
		<div class="list-group-item">
			<div class="row align-items-center">
				<div class="col fw-bold">
					<?= $model->getAttributeLabel('balance') ?>
				</div>
				<div class="col-auto">
					<?= Yii::$app->formatter->asCurrency($model->balance) ?>
				</div>
			</div>
		</div>
		<?php if (!empty($model->currency)): ?>
			<div class="list-group-item">
				<div class="row align-items-center">
					<div class="col fw-bold">
						<?= $model->getAttributeLabel('currency') ?>
					</div>
					<div class="col-auto">
						<?= Html::encode($model->currency) ?>
					</div>
				</div>
			</div>
		<?php endif; ?>
		<?php if (!empty($model->reference_id)): ?>
			<div class="list-group-item">
				<div class="row align-items-center">
					<div class="col fw-bold">
						<?= $model->getAttributeLabel('reference_id') ?>
					</div>
					<div class="col-auto">
						<?= Html::encode($model->reference_id) ?>
					</div>
				</div>
			</div>
		<?php endif; ?>
		<?php if (!empty($model->description)): ?>
			<div class="list-group-item">
				<div class="row align-items-center">
					<div class="col fw-bold">
						<?= $model->getAttributeLabel('description') ?>
					</div>
					<div class="col-auto">
						<?= nl2br($model->description) ?>
					</div>
				</div>
			</div>
		<?php endif; ?>
		<?php if (!empty($model->note)): ?>
			<div class="list-group-item">
				<div class="row align-items-center">
					<div class="col fw-bold">
						<?= $model->getAttributeLabel('note') ?>
					</div>
					<div class="col-auto">
						<?= $model->note ?>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>

<?php if ($params = $model->params): ?>
	<div class="list-group transaction-params">
		<?php foreach ($params as $label => $value): ?>
			<div class="list-group-item">
				<div class="row align-items-center">
					<div class="col fw-bold mb-2">
						<?= Inflector::humanize($label, TRUE) ?>
					</div>
					<div class="col-auto text-break">
						<?php if (is_array($value)){
							if (!empty($value['format']) && !empty($value['content'])){
								$function = 'as' . ucfirst($value['format']);
								if (method_exists(Yii::$app->formatter, $function)){
									echo call_user_func_array([Yii::$app->formatter, $function],
										[$value['content']]);
								}
							}else{
								echo Html::tag('code', Json::encode($value, JSON_PRETTY_PRINT));
							}

						}else{
							echo Html::encode($value);
						} ?>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>

<?php
if ($model->canUpdateStatus()){
	echo Html::a(Yii::t('wallet', 'Update this transaction'),
		['update', 'id' => $model->id],
		['class' => 'btn btn-warning d-block mt-4', 'data-bs-toggle' => 'modal', 'data-bs-target' => '#confirm-modal', 'data-header' => Yii::t('wallet',
			'Update transaction')]);
}

if ($model->canReturn()){
	echo Html::a(Yii::t('wallet', 'Refund for this transaction'),
		['operator', 'id' => $model->id, 'action' => 'refund'],
		['class' => 'btn btn-danger d-block mt-4', 'data-bs-toggle' => 'modal', 'data-bs-target' => '#confirm-modal', 'data-header' => Yii::t('wallet',
			'Refund for this transaction')]);
}

if ($model->canReturnTransfer()){
	echo Html::a(Yii::t('wallet', 'Refund for this transaction'),
		['refund-transfer', 'id' => $model->id, 'wallet' => $model->wallet_sub_id ?? NULL],
		['class' => 'btn btn-danger d-block mt-4', 'data-bs-toggle' => 'modal', 'data-bs-target' => '#confirm-modal', 'data-header' => Yii::t('wallet',
			'Refund for this transaction')]);
}

if ($model->needApproval()){
	echo Html::a(Yii::t('wallet', 'Approve this transaction'),
		['operator', 'id' => $model->id, 'action' => 'approve'],
		['class' => 'btn btn-primary d-block mt-4', 'data-bs-toggle' => 'modal', 'data-bs-target' => '#confirm-modal', 'data-header' => Yii::t('wallet',
			'Approve this transaction')]);

	echo Html::a(Yii::t('wallet', 'Reject this transaction'),
		['operator', 'id' => $model->id, 'action' => 'reject'],
		['class' => 'btn btn-link d-block mt-2', 'data-bs-toggle' => 'modal', 'data-bs-target' => '#confirm-modal', 'data-header' => Yii::t('wallet',
			'Reject this transaction')]);
}

$js = <<<JS
	$('#confirm-modal').on('show.bs.modal', function (e) {
	    var button = $(e.relatedTarget);
	    var href = button.attr('href');
	
	    if (typeof href !== 'undefined') {
	        var modal = $(this);
	        modal.find('.modal-body').html('<div class="spinner-grow text-primary" role="status">' +
	            '        <span class="sr-only">Loading...</span>' +
	            '    </div>');
	
	        if (button.data('header')) {
	            modal.find('.modal-header .modal-title').text(button.data('header'));
	        }
	
	        $.ajax({
	            type: 'POST',
	            url: href,
	            success: function (result) {
	                modal.find('.modal-body').html(result);
	            }
	        });
	    }
	});
JS;
$this->registerJs($js, View::POS_READY, 'approve-modal');