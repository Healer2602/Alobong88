<?php

use common\models\Language;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var \modules\website\models\Menu $model
 * @var yii\bootstrap5\ActiveForm $form
 */
?>

<div class="menu-form">

	<?php $form = ActiveForm::begin([
		'id' => 'menu-form',
	]); ?>

	<?= $form->field($model, 'name')->textInput() ?>

	<?= $form->field($model, 'position')
	         ->dropDownList($model->positions,
		         ['class' => 'form-select', 'data-toggle' => 'select', 'prompt' => 'None']) ?>

	<?= $form->field($model, 'language')
	         ->dropDownList(Language::listLanguage(),
		         ['class' => 'form-select', 'data-toggle' => 'select']) ?>

    <div class="form-group">
		<?= Html::submitButton(Yii::t('common', 'Save'),
			['class' => 'btn btn-primary btn-block']) ?>
		<?= Html::a(Yii::t('common', 'Cancel'), ['#'],
			['class' => 'btn btn-block btn-link text-muted', 'data-dismiss' => "modal"]) ?>
    </div>

	<?php ActiveForm::end(); ?>

</div>

<?php

$js = <<<JS
	$.select2($('#menu-form .form-select'));
JS;
$this->registerJs($js);