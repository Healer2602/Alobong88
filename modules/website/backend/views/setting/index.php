<?php

use modules\media\widgets\MediaInputModal;
use modules\spider\lib\ace\AceAsset;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var \modules\website\models\WebsiteSetting $model
 */

$this->title = Yii::t('website', 'Website Setting');

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('website', 'Settings'),
	'url'   => ['/setting/index']
];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $form = ActiveForm::begin() ?>
	<div class="row justify-content-around">
		<div class="col-lg-6">
			<div class="m-portlet h-100">
				<?= $form->field($model, 'site_logo')
				         ->widget(MediaInputModal::class, [
					         'current_path' => 'logo'
				         ]); ?>

				<?= $form->field($model, 'site_favicon')
				         ->widget(MediaInputModal::class, [
					         'current_path' => 'logo'
				         ]); ?>

				<?= $form->field($model, 'social_image')
				         ->widget(MediaInputModal::class, [
					         'current_path' => 'logo'
				         ]); ?>

				<?= $form->field($model, 'gtm') ?>
				<?= $form->field($model, 'admin_email') ?>

				<div class="form-group">
					<?= Html::submitButton(Yii::t('common', 'Update'),
						['class' => 'btn btn-primary w-100 mb-2']) ?>
					<?= Html::a(Yii::t('common', 'Cancel'), [''],
						['class' => 'btn btn-link text-muted w-100']) ?>
				</div>
			</div>
		</div>
	</div>

<?php ActiveForm::end() ?>

<?php
$asset = AceAsset::register($this);

$js = <<<JS
    var editor = ace.edit("javascript");
    ace.config.set('basePath', '{$asset->baseUrl}');
    editor.setTheme("ace/theme/github");
    editor.session.setMode("ace/mode/html");
    
    editor.session.on('change', function(delta) {
        $('#field-javascript').val(editor.getValue());
    });
JS;
$this->registerJs($js);