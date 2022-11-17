<?php

use common\widgets\Alert;
use modules\media\widgets\MediaInputModal;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var yii\bootstrap5\ActiveForm $form
 * @var \modules\website\models\Menu $model
 * @var \modules\website\models\Menu $parent
 */
?>

<div class="menu-item-form">

	<?= Yii::$app->request->isAjax ? Alert::widget() : '' ?>

	<?php $form = ActiveForm::begin([
		'id' => 'menu-item-form',
	]); ?>

	<?= $form->field($model, 'name')->textInput() ?>

	<?= $form->field($model, 'menu_path')->textInput() ?>

	<?= $form->field($model, 'parent_id')
	         ->dropDownList($parent->getMenuOptions($model->id),
		         ['class' => 'form-select', 'data-toggle' => 'select']) ?>

	<?= $form->field($model, 'params[style]')
	         ->dropDownList($model->styles)->label('Kiểu') ?>

	<?= $form->field($model, 'params[new_tab]')
	         ->checkbox()->label('Open new tab') ?>

	<?= $form->field($model, 'params[no_follow]')
	         ->checkbox()->label('This is nofollow link') ?>

	<?= $form->field($model, 'icon')->widget(MediaInputModal::class, [
		'current_path' => 'menu',
		'target'       => '#media-modal'
	]) ?>

    <div class="form-group">
		<?= Html::submitButton(Yii::t('common', 'Save'),
			['class' => 'btn btn-primary btn-block']) ?>
		<?= Html::a(Yii::t('common', 'Close'), Yii::$app->request->referrer,
			['class' => 'btn btn-link btn-block text-muted']) ?>
    </div>

	<?php ActiveForm::end(); ?>

</div>

<?php

$js = <<<JS
	$.select2($('#menu-item-form .form-select'));
JS;
$this->registerJs($js);