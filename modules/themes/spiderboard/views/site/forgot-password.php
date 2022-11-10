<?php

/**
 * @var \yii\web\View $this
 * @var yii\bootstrap5\ActiveForm $form
 * @var \backend\models\ForgotPasswordForm $model
 */

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Forgot Password');

?>

<p class="card-title mb-4">Please fill out your email. A link to reset password will be sent there.</p>

<?php $form = ActiveForm::begin(['id' => 'forgot-password-form']); ?>

<?= $form->field($model, 'email', ['template' => "{input}\n{hint}\n{error}"])
         ->textInput(['placeholder' => $model->getAttributeLabel('email')]) ?>

<?= Html::submitButton('Send', ['class' => 'btn btn-lg btn-primary w-100 mb-3']) ?>

<?= Html::a('Back to login', ['site/login']) ?>

<?php ActiveForm::end(); ?>
