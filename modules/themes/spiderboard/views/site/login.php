<?php

/**
 * @var yii\web\View $this
 * @var yii\bootstrap5\ActiveForm $form
 * @var \backend\models\LoginForm $model
 */

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

$this->title                   = Yii::t('common', 'Free access to our dashboard');
$this->params['breadcrumbs'][] = $this->title;
?>

<!-- Subheading -->
<p class="text-muted text-center mb-5"><?= Html::encode($this->title) ?></p>

<?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

<?= $form->field($model, 'username') ?>

<?= $form->field($model, 'password')->passwordInput() ?>

<?= $form->field($model, 'rememberMe')->checkbox() ?>

<?= Html::submitButton('Login', ['class' => 'btn btn-lg btn-primary w-100 mb-3']) ?>

<div class="text-center">
	<small class="text-muted">
		<?= Html::a(Yii::t('common', 'Forgot Password?'), ['site/forgot-password']) ?>
	</small>
</div>

<?php ActiveForm::end(); ?>
