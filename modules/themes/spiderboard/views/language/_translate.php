<?php

use modules\spider\lib\ace\AceAsset;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/**
 * @var \backend\base\View $this
 * @var \common\models\Language $model
 * @var \backend\models\StringTranslate $translate
 */
?>

<?php $form = ActiveForm::begin([
	'action' => ['translate', 'id' => $model->id]
]); ?>
	<div class="row justify-content-center">
		<div class="col-md-8">
			<?= Html::activeTextarea($translate, 'translation',
				['id' => 'field-editor', 'class' => 'd-none']) ?>

			<?= $form->field($translate, 'translation')
			         ->textarea(['id' => 'editor', 'rows' => 50])->label(FALSE) ?>

			<?= Html::error($translate, 'translation',
				['class' => 'invalid-feedback d-block mt-n3 mb-3']) ?>

		</div>
		<div class="col-md-6">
			<div class="form-group">
				<?= Html::submitButton(Yii::t('common', 'Update'),
					['class' => 'btn btn-primary w-100']) ?>

				<?= Html::a(Yii::t('common', 'Cancel'), ['index'],
					['class' => 'btn btn-link text-muted w-100']) ?>
			</div>
		</div>
	</div>
<?php ActiveForm::end(); ?>

<?php
$asset = AceAsset::register($this);
$js    = <<< JS
	var editor = ace.edit("editor");
    ace.config.set('basePath', '{$asset->baseUrl}');
    editor.setTheme("ace/theme/github");
    editor.session.setMode("ace/mode/yaml");
    editor.session.on('change', function(delta) {
        $('#field-editor').val(editor.getValue());
    });
    editor.setOptions({
        maxLines: Infinity,
        minLines: 40
    });
JS;

$this->registerJs($js);
