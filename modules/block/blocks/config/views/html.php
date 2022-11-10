<?php

/**
 * @var yii\web\View $this
 * @var \modules\block\models\Block $model
 * @var array $categories
 */

use modules\spider\lib\ace\AceAsset;
use yii\bootstrap5\Html;


?>
	<div class="form-group">
		<?= Html::activeLabel($model, 'content') ?>
		<?= Html::activeTextarea($model, 'content',
			['id' => 'field-editor', 'class' => 'd-none']) ?>
		<?= Html::activeTextarea($model, 'content',
			['id' => 'editor', 'rows' => 20]) ?>
		<?= Html::error($model, 'content', ['class' => 'invalid-feedback']) ?>
	</div>

<?php
$asset = AceAsset::register($this);

$js = <<<JS
    var editor = ace.edit("editor");
    ace.config.set('basePath', '{$asset->baseUrl}');
    editor.setTheme("ace/theme/github");
    editor.session.setMode("ace/mode/html");
    editor.session.on('change', function(delta) {
        $('#field-editor').val(editor.getValue());
    });
JS;
$this->registerJs($js);