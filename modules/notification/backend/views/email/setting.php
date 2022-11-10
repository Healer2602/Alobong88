<?php

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var \modules\notification\models\EmailSetting $model
 */

$this->title = Yii::t('common', 'Email Setting');

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('common', 'Settings'),
	'url'   => ['/setting/index']
];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $form = ActiveForm::begin() ?>
	<div class="row justify-content-center">
		<div class="col-lg-6">
			<div class="row">
				<div class="col-md-6"><?= $form->field($model, 'email_smtp_server') ?></div>
				<div class="col-md-3"><?= $form->field($model, 'email_smtp_port') ?></div>
				<div class="col-md-3"><?= $form->field($model, 'email_smtp_protocol')
				                               ->dropDownList(['' => 'No', 'SSL' => 'SSL', 'TLS' => 'TLS']) ?></div>
			</div>

			<?= $form->field($model, 'email_smtp_username') ?>
			<?= $form->field($model, 'email_smtp_password')->passwordInput() ?>
			<?= $form->field($model, 'email_html')->checkbox() ?>

			<hr>
			<?= $form->field($model, 'email_sender') ?>
			<?= $form->field($model, 'email_sender_name') ?>
			<?= $form->field($model, 'email_tester') ?>

			<div class="mt-5">
				<?= Html::submitButton(Yii::t('common', 'Update'),
					['class' => 'btn btn-primary']) ?>
				<?= Html::a(Yii::t('common', 'Cancel'), [''],
					['class' => 'btn btn-secondary']) ?>
			</div>
		</div>
	</div>
<?php ActiveForm::end() ?>