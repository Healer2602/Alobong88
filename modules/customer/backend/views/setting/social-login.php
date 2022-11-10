<?php

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var \modules\customer\models\SocialLoginSetting $model
 */

$this->title = Yii::t('customer', 'Social Login Settings');

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('common', 'Settings'),
	'url'   => ['/setting/index']
];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $form = ActiveForm::begin() ?>
	<div class="row justify-content-around">
		<div class="col-md-6">
			<div class="m-portlet">
				<?= $form->field($model, 'google_client')->textInput() ?>
				<?= $form->field($model, 'google_secret')->textInput() ?>

				<?= $form->field($model, 'facebook_client')->textInput() ?>
				<?= $form->field($model, 'facebook_secret')->textInput() ?>

				<div class="form-group">
					<?= Html::submitButton(Yii::t('common', 'Update'),
						['class' => 'btn btn-primary w-100 mb-2']) ?>
					<?= Html::a(Yii::t('common', 'Cancel'), ['index'],
						['class' => 'btn btn-link w-100']) ?>
				</div>
			</div>
		</div>
	</div>

<?php ActiveForm::end() ?>