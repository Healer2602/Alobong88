<?php

/**
 * @var \yii\web\View $this
 * @var yii\bootstrap5\ActiveForm $form
 * @var \backend\models\ResetPasswordForm $model
 */

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Set new password');

?>

	<p class="mb-4"><?= Yii::t('app', 'Please choose your new password:') ?></p>

<?php $form = ActiveForm::begin(['id' => 'forgot-password-form']); ?>

<?= $form->field($model, 'password', ['template' => "{input}\n{hint}\n{error}"])
         ->passwordInput(['placeholder' => $model->getAttributeLabel('password')]) ?>

<?= $form->field($model, 'confirm_password', ['template' => "{input}\n{hint}\n{error}"])
         ->passwordInput(['placeholder' => $model->getAttributeLabel('confirm_password')]) ?>

<?= Html::submitButton('Save', ['class' => 'btn btn-lg btn-primary w-100 mb-3']) ?>

<?= Html::a('Back to login', ['login']) ?>

<?php ActiveForm::end(); ?>