<?php

/**
 * @var yii\web\View $this
 * @var array $statuses
 * @var \yii\base\DynamicModel $model
 */

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

$form = ActiveForm::begin([
	'id' => 'reject-form'
]); ?>
	<div class="row">
		<div class="col-md-12">
			<?= $form->field($model, 'reason')
			         ->textarea(['rows' => 8]);
			?>
			<div class="form-group">
				<?= Html::submitButton(Yii::t('customer', 'Confirm'),
					['class' => 'btn btn-primary w-100']); ?>
				<?= Html::a(Yii::t('common', 'Cancel'), ['#'],
					['class' => 'btn w-100 btn-link text-muted', 'data-bs-dismiss' => "modal"]) ?>
			</div>
		</div>
	</div>
<?php ActiveForm::end();

