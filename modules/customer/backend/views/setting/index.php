<?php

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var \modules\customer\models\Setting $model
 * @var string $active
 * @var array $pages
 */

$this->title = Yii::t('customer', 'Players');

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('common', 'Settings'),
	'url'   => ['/setting/index']
];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $form = ActiveForm::begin() ?>
	<div class="row justify-content-around">
		<div class="col-lg-7 col-xl-5">
			<div class="m-portlet">
				<?= $form->field($model, 'currencies')
				         ->dropDownList($model->listCurrency ?? [], [
					         'data-toggle' => 'tags',
					         'class'       => 'form-select',
					         'multiple'    => TRUE
				         ]) ?>

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