<?php

use backend\base\GridView;
use yii\bootstrap5\ActiveForm;
use yii\grid\SerialColumn;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var \modules\agent\models\Agent $model
 * @var \yii\data\ActiveDataProvider $customers
 */

$this->title = Yii::t('agent', 'Agent: {0}', [$model->email]);

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('agent', 'Agent'),
	'url'   => ['index']
];
$this->params['breadcrumbs'][] = $this->title;
?>

	<div class="coupon-update">
		<div class="row">
			<div class="col-lg-4">
				<div class="card">
					<div class="card-header">
						<h4 class="card-header-title"><?= Yii::t('agent', 'Information') ?></h4>
					</div>
					<div class="card-body pb-0">
						<div class="agent-form">
							<?php $form = ActiveForm::begin(); ?>

							<?= $form->field($model, 'name') ?>

							<?= $form->field($model, 'email') ?>

							<?= $form->field($model, 'code', [
								'inputTemplate' => '<div class="input-group">{input}<button class="btn btn-outline-secondary generate" type="button">Generate</button></div>'
							]) ?>

							<?= $form->field($model, 'status')->dropDownList($model::statuses()) ?>

							<?php if (!$model->isNewRecord){
								echo $form->field($model, 'link', [
									'inputTemplate' => '<div class="input-group">{input}<button class="btn btn-copy btn-outline-secondary" data-clipboard-target="#agent-link" type="button" title="Copied">Copy</button></div>'
								])->textInput(['id' => 'agent-link', 'readonly' => TRUE]);
							} ?>

							<div class="form-group">
								<?= Html::submitButton(Yii::t('common', 'Save'),
									['class' => 'btn btn-primary btn-block']) ?>
							</div>

							<?php ActiveForm::end(); ?>
						</div>
					</div>
				</div>

				<div class="card">
					<div class="card-header">
						<h4 class="card-header-title"><?= Yii::t('agent',
								'Commission Plans') ?></h4>
					</div>
					<div class="card-body pb-0">
						<div class="agent-form">
							<?php $form = ActiveForm::begin(); ?>

							<?= $form->field($model, 'active') ?>

							<?= $form->field($model, 'deposit_rate') ?>

							<?= $form->field($model, 'withdrawal_rate') ?>

							<?= $form->field($model, 'administration_rate') ?>

							<h3><?= Yii::t('agent', 'Commission Rate') ?></h3>

							<?= $form->field($model, 'range_1')->label($model->ranges[1] ?? TRUE) ?>

							<?= $form->field($model, 'range_2')->label($model->ranges[2] ?? TRUE) ?>

							<?= $form->field($model, 'range_3')->label($model->ranges[3] ?? TRUE) ?>

							<div class="form-group">
								<?= Html::submitButton(Yii::t('common', 'Save'),
									['class' => 'btn btn-primary btn-block']) ?>
							</div>

							<?php ActiveForm::end(); ?>
						</div>
					</div>
				</div>
			</div>
			<div class="col-lg-8">
				<div class="card">
					<div class="card-header">
						<h4 class="card-header-title"><?= Yii::t('agent', 'Players') ?></h4>
					</div>
					<div class="card-header card-filter">
						<div class="filter mb-0 pt-2">
							<form class="form-inline" action="" method="get">
								<div class="input-group">
									<?= Html::textInput('s', $filtering['s'] ?? NULL,
										['class' => 'form-control', 'placeholder' => Yii::t('wallet',
											'Search by player')]) ?>
								</div>
								<div class="input-group">
									<?= Html::textInput('date',
										$filtering['date'] ?? NULL,
										[
											'class'          => 'form-control',
											'data-flatpickr' => '{"mode": "range"}',
											'placeholder'    => Yii::t('wallet', 'Transaction Date')
										]) ?>
								</div>
								<div class="input-group">
									<div class="input-group-btn">
										<button class="btn btn-outline-primary" type="submit">
											<i class="fe fe-search" aria-hidden="true"></i>
										</button>
										<button class="btn btn-outline-secondary clear" type="button">
											<i class="fe fe-x" aria-hidden="true"></i></button>
									</div>
								</div>
							</form>
						</div>
					</div>
					<?= GridView::widget([
						'options'      => ['class' => 'table-responsive'],
						'layout'       => "{items}<div class='p-3 d-flex justify-content-end'>{pager}</div>{summary}",
						'dataProvider' => $customers,
						'tableOptions' => ['class' => 'table table-nowrap table-hover card-table'],
						'columns'      => [
							[
								'class' => SerialColumn::class
							],
							'name',
							'email',
							[
								'attribute' => 'winloss',
								'format'    => 'currency'
							],
							[
								'attribute' => 'turnover',
								'format'    => 'currency'
							],
							[
								'attribute'      => 'updated_at',
								'format'         => 'relativeTime',
								'contentOptions' => ['class' => 'text-nowrap']
							]
						]
					]) ?>
				</div>
			</div>
		</div>
	</div>

<?php
$js = <<<JS
$('.generate').on('click', function(){
   var coupon = generateAgent(4);
   $(this).parents('.form-group').find('.form-control').val(coupon);
});

function generateAgent(length) {
  var text = "";
  var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
  for (var i = 0; i < length; i++)
    text += possible.charAt(Math.floor(Math.random() * possible.length));
  return text;
}

	if(ClipboardJS.isSupported()){
		var _tooltip = new bootstrap.Tooltip(document.querySelector('.btn-copy'), {
			"trigger": "manual"
		});
    
    	function setTooltip(element, message) {
			$(element).tooltip('hide').attr('data-bs-title', message).tooltip('show');
		}
		
		function hideTooltip() {
			setTimeout(function() {
				$('.btn-copy').tooltip('hide');
			}, 1000);
		}
		
    	var clipboard = new ClipboardJS('.btn-copy');
		clipboard.on('success', function(e) {
			setTooltip(e.trigger, 'Copied');
			hideTooltip();
		});
	}else{
    	$('.clipboard').hide();
	}

JS;
$this->registerJs($js);
$css = <<<CSS
.invalid-feedback:not(:empty){
    display: block;
}
CSS;
$this->registerCss($css);
