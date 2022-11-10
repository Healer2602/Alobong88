<?php

use modules\spider\lib\ace\AceAsset;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var \modules\website\models\HtaccessSetting $model
 */

$this->title = Yii::t('website', 'Update .htaccess file');

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('website', 'Settings'),
	'url'   => ['/setting/index']
];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $form = ActiveForm::begin() ?>

	<div class="row justify-content-around">
		<div class="col-lg-7">
			<div class="m-portlet h-100">
				<?= Html::activeTextarea($model, 'content',
					['id' => 'field-editor', 'class' => 'd-none']) ?>

				<?= $form->field($model, 'content')->textarea(['id' => 'editor', 'rows' => 20]) ?>

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
$js    = <<<JS
    var editor = ace.edit("editor");
    ace.config.set('basePath', '{$asset->baseUrl}');
    editor.setTheme("ace/theme/github");
    editor.session.setMode("ace/mode/sh");
    editor.session.on('change', function(delta) {
        $('#field-editor').val(editor.getValue());
    });
JS;
$this->registerJs($js);

