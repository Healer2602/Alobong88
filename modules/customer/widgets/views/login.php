<?php

/**
 * @var frontend\base\View $this
 * @var yii\bootstrap5\ActiveForm $form
 * @var \modules\customer\frontend\models\LoginForm $model
 */

use modules\spider\recaptcha\InputWidget;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Url;

?>

<div class="login-form desktop">
	<?php $form = ActiveForm::begin([
		'id'          => 'login-form',
		'options'     => ['class' => 'row g-3 align-items-center me-2'],
		'action'      => ['/customer/default/sign-in'],
		'fieldConfig' => [
			'template' => "{input}",
		]
	]); ?>

	<?= $form->field($model, 'username', ['options' => ['class' => 'col']])
	         ->textInput(['placeholder' => $model->getAttributeLabel('username'), 'class' => 'form-control form-control-rounded']) ?>

	<?= $form->field($model, 'password', ['options' => ['class' => 'col']])
	         ->passwordInput(['placeholder' => $model->getAttributeLabel('password'), 'class' => 'form-control form-control-rounded']) ?>

	<div class="col-2">
		<button type="submit" class="btn btn-outline-primary btn-round"><?= Yii::t('customer',
				'Login') ?></button>

		<?= $form->field($model, 'captcha', ['options' => ['class' => 'mb-0']])
		         ->widget(InputWidget::class) ?>
	</div>

	<div class="col-2">
		<a class="btn btn-primary btn-round" href="<?= Url::to(['/customer/default/register']) ?>"><?= Yii::t('customer',
				'Sign Up') ?></a>
	</div>

	<?php ActiveForm::end() ?>
</div>

<div class="tablet me-2 ms-auto">
	<div class="row">
		<div class="col-6 pe-0">
			<a class="btn btn-outline-primary btn-round" type="submit" href="<?= Url::to(['/customer/register']) ?>"><?= Yii::t('customer',
					'Sign up') ?></a>
		</div>
		<div class="col-6">
			<a class="btn btn-primary btn-round" type="submit" href="<?= Url::to(['/customer/sign-in']) ?>"><?= Yii::t('customer',
					'Login') ?></a>
		</div>
	</div>
</div>