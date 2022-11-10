<?php
/**
 * @var frontend\base\View $this
 * @var \modules\wallet\frontend\models\Deposit $model
 */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

?>

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
	'action'      => ['/wallet/default/deposit', 'opt' => $model->opt]
]);
?>

<?php if (count($model->channels) > 1): ?>
	<div class="custom-options color mb-4">
		<div class="row align-items-center">
			<div class="col-lg-3 mb-lg-0 mb-2">
				<?= Html::label(Yii::t('wallet', 'Deposit Channel')) ?>
			</div>
			<div class="col-lg-9">
				<div class="row row-cols-3 row-cols-md-4 g-2">
					<?php foreach ($model->channels as $channel): ?>
						<div class="col">
							<div class="form-check deposit-channel">
								<?= Html::activeRadio($model, 'gateway', [
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
									echo Yii::t('wallet', $channel['title']);
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
	<div class="deposit-channel d-none">
		<?= Html::activeRadio($model, 'gateway',
			['checked' => TRUE, 'value' => $model->channels[0]['key'] ?? NULL]) ?>
	</div>
<?php endif; ?>

	<div class="form-deposit">
		<?php
		Pjax::begin([
			'id'                 => 'pjax-deposit-form',
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

<?php
$deposit_url = Url::to(['default/deposit-form', 'opt' => $model->opt]);
$js          = <<<JS
	$(".deposit-channel").click(function(e) {
		 e.preventDefault();
		 let channel = $(this).find(':input[type=radio]');
		 channel.prop('checked',true);
		$.pjax.reload({
		    container:"#pjax-deposit-form",
		    data: {id:channel.val()},
		    url:  "{$deposit_url}",
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