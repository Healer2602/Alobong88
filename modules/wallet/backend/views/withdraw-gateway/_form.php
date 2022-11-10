<?php

use kartik\depdrop\DepDrop;
use modules\media\widgets\MediaInputModal;
use modules\wallet\models\Gateway;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var \backend\base\View $this
 * @var \modules\wallet\models\Gateway $model
 */
?>

<?php $form = ActiveForm::begin([
	'id' => 'gateway_form'
]); ?>

<?= $form->field($model, 'option')
         ->dropDownList($model->getOptions(), [
	         'class'       => 'form-select',
	         'data-toggle' => 'select'
         ]) ?>

<?= $form->field($model, 'key')
         ->dropDownList($model->getNewGateways(), [
	         'class'       => 'form-select',
	         'data-toggle' => 'select',
	         'id'          => 'rate_source_id_ipt'
         ]) ?>

<?= $form->field($model, 'title')->textInput() ?>

<?= $form->field($model, 'icon')
         ->widget(MediaInputModal::class, [
	         'current_path' => 'withdraw-gateway'
         ]) ?>

<?= $form->field($model, 'currency')
         ->widget(DepDrop::class, [
	         'pluginOptions' => [
		         'depends'     => ['rate_source_id_ipt'],
		         'url'         => Url::to(['support-coin', 'selected' => $model->currency]),
		         'initialize'  => TRUE,
		         'placeholder' => FALSE
	         ],
	         'options'       => [
		         'class'       => 'form-select',
		         'data-toggle' => 'select',
		         'multiple'    => TRUE
	         ]
         ]) ?>

<?= $form->field($model, 'fee')->begin() ?>
<?= Html::activeLabel($model, 'withdraw_fee', ['class' => 'form-label']) ?>
	<div class="input-group input-group-merge">
		<?= Html::activeTextInput($model, 'fee',
			['class' => 'form-control form-control-prepended']) ?>

		<div class="input-group-text rounded-end">
			<span class="fe fe-percent"></span>
		</div>

		<?= Html::error($model, 'fee', ['class' => 'invalid-feedback']) ?>
	</div>
<?= $form->field($model, 'fee')->end() ?>

<?= $form->field($model, 'endpoint')->textInput() ?>

<?= $form->field($model, 'api_key')->textInput() ?>

<?= $form->field($model, 'api_secret')->textInput() ?>

<?= $form->field($model, 'ranks')
         ->dropDownList($model->customerRankList, [
	         'class'       => 'form-select',
	         'data-toggle' => 'select',
	         'multiple'    => TRUE
         ]) ?>

<?= $form->field($model, 'status')
         ->dropDownList(Gateway::statuses(),
	         ['class' => 'form-select', 'data-toggle' => 'select']) ?>

	<div class="form-group mt-4">
		<?= Html::submitButton(Yii::t('common',
			$model->isNewRecord ? 'Create' : 'Update'),
			['class' => 'btn btn-primary w-100']) ?>

		<?= Html::a(Yii::t('common', 'Cancel'), ['index'],
			['class' => 'btn btn-link text-muted w-100']) ?>
	</div>

<?php ActiveForm::end(); ?>