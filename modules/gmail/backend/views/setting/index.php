<?php

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Alert;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var \modules\gmail\models\GoogleSetting $model
 */

$this->title = Yii::t('gmail', 'Gmail API Setting');

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('gmail', 'Settings'),
	'url'   => ['/setting/index']
];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="row justify-content-around">
	<div class="col-lg-6">
		<?php $form = ActiveForm::begin() ?>
		<?= $form->field($model, 'client_id') ?>
		<?= $form->field($model, 'client_secret')->passwordInput() ?>
		<?= $form->field($model, 'email_sender') ?>
		<?= $form->field($model, 'email_sender_name') ?>
		<?= $form->field($model, 'redirectUri')->textInput(['readonly' => TRUE]) ?>

		<?php
		if ($authorization = $model->hasAuthorization()){
			if (!empty($authorization['error'])){
				echo Alert::widget([
					'body'        => $authorization['error'],
					'options'     => ['class' => 'alert alert-danger'],
					'closeButton' => FALSE
				]);
			}else{
				echo Html::beginTag('div', ['class' => 'mb-4']);
				echo Html::submitButton(Yii::t('gmail',
					'Remove connection'),
					['class' => 'btn btn-danger w-100', 'name' => 'submit', 'value' => $model::ACTION_REMOVE]);
				if (!empty($model->connection['email'])){
					echo Html::tag('p', Yii::t('gmail', 'Connected as {0}',
						[$model->connection['email']]), ['class' => 'text-muted']);
				}
				echo Html::endTag('div');
			}
		}else{
			echo Html::submitButton(Yii::t('gmail',
				'Allow to send emails using your Google Account'),
				['class' => 'btn btn-info w-100 mb-4', 'name' => 'submit', 'value' => $model::ACTION_CONNECT]);
		}
		?>

		<div class="form-group">
			<?= Html::submitButton(Yii::t('gmail', 'Update'),
				['class' => 'btn btn-primary', 'name' => 'submit']) ?>
			<?= Html::a(Yii::t('gmail', 'Cancel'), ['/setting/index'],
				['class' => 'btn btn-secondary']) ?>
		</div>
		<?php ActiveForm::end() ?>
		<?php if ($model->hasAuthorization()): ?>
			<hr>        <h3>Test Email</h3>
			<?php $form = ActiveForm::begin(['action' => ['test']]) ?>
			<div class="form-group">
				<?php echo Html::input('email',
					'email', NULL, ['class' => 'form-control', 'required' => TRUE]) ?>
			</div>
			<?= Html::submitButton(Yii::t('gmail', 'Send'),
				['class' => 'btn btn-primary']) ?><?php ActiveForm::end() ?>

		<?php endif; ?>
	</div>
</div>
